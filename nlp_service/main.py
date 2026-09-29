from fastapi import FastAPI
from pydantic import BaseModel
import pickle
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
import re
import os

app = FastAPI(title="Chatbot NLP Core")

# Load NLP Model
MODEL_PATH = "model.pkl"
model = None
if os.path.exists(MODEL_PATH):
    with open(MODEL_PATH, 'rb') as f:
        model = pickle.load(f)

# Stemmer setup
factory = StemmerFactory()
stemmer = factory.create_stemmer()

def preprocess_text(text):
    text = text.lower()
    text = re.sub(r'[^\w\s]', ' ', text)
    return stemmer.stem(text)

class ParseRequest(BaseModel):
    text: str
    session_state: dict = {}

class ParseResponse(BaseModel):
    intent: str
    confidence: float
    entities: dict

def extract_entities(text: str, state: dict) -> dict:
    entities = {}
    text_lower = text.lower()
    
    # 1. Regex Extraction
    # Email
    email_match = re.search(r'[\w\.-]+@[\w\.-]+\.\w+', text)
    if email_match:
        entities['email'] = email_match.group(0)
    
    # Phone (Indonesian format common)
    phone_match = re.search(r'(\+62|62|0)8[1-9][0-9]{6,10}', re.sub(r'[\s-]', '', text))
    if phone_match:
        entities['telepon'] = phone_match.group(0)
        
    # Date (dd-mm-yyyy, dd/mm/yyyy, or text)
    date_match = re.search(r'\d{1,2}[\s/-]+(?:[a-zA-Z]+|\d{1,2})[\s/-]+\d{2,4}', text)
    if date_match:
        entities['tanggal_lahir'] = date_match.group(0)

    # 2. Dictionary / Rule based matching
    if re.search(r'\b(laki-laki|laki|cowo|cowok|pria|l)\b', text_lower):
        entities['jenis_kelamin'] = 'laki-laki'
    elif re.search(r'\b(perempuan|cewe|cewek|wanita|p)\b', text_lower):
        entities['jenis_kelamin'] = 'perempuan'
        
    if re.search(r'\b(smp|mts|sekolah menengah pertama)\b', text_lower):
        entities['jenjang'] = 'SMP'
    elif re.search(r'\b(sma|smk|ma|sekolah menengah atas)\b', text_lower):
        entities['jenjang'] = 'SMA'

    # 3. Position / State based extraction (Slot Filling)
    # If Laravel says we are currently asking for 'nama', we aggressively capture it
    if state.get('awaiting_slot') == 'nama':
        entities['nama'] = text.strip()
    elif state.get('awaiting_slot') == 'alamat':
        entities['alamat'] = text.strip()
    elif state.get('awaiting_slot') == 'asal_sekolah':
        entities['asal_sekolah'] = text.strip()
    elif state.get('awaiting_slot') == 'nama_wali':
        entities['nama_wali'] = text.strip()

    return entities

@app.post("/parse", response_model=ParseResponse)
def parse_text(request: ParseRequest):
    if not model:
        return ParseResponse(intent="tidak_dikenali", confidence=0.0, entities={})
        
    # 1. Intent Classification
    clean_text = preprocess_text(request.text)
    
    # LinearSVC doesn't provide predict_proba out of the box nicely, 
    # but we can get decision function distance to estimate confidence.
    # For a simple approach, we just return the predicted intent.
    prediction = model.predict([clean_text])[0]
    
    decision_scores = model.decision_function([clean_text])[0]
    confidence = max(decision_scores) if hasattr(decision_scores, "__iter__") else 1.0
    
    # Normalize confidence loosely (LinearSVC outputs raw margin scores)
    # If confidence is too low, we fallback
    if confidence < -0.5: # Adjust threshold based on evaluation
        prediction = "tidak_dikenali"
        
    # 2. Entity Extraction
    entities = extract_entities(request.text, request.session_state)
    
    return ParseResponse(
        intent=prediction,
        confidence=float(confidence),
        entities=entities
    )

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)

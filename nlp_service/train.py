import json
import pickle
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.svm import LinearSVC
from sklearn.pipeline import Pipeline
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
import re

print("Loading dataset...")
with open('dataset.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

df = pd.DataFrame(data)

# Preprocessing
factory = StemmerFactory()
stemmer = factory.create_stemmer()

def preprocess_text(text):
    text = text.lower()
    text = re.sub(r'[^\w\s]', ' ', text)
    text = stemmer.stem(text)
    return text

print("Preprocessing text...")
df['clean_text'] = df['text'].apply(preprocess_text)

# Modeling
print("Training model...")
pipeline = Pipeline([
    ('tfidf', TfidfVectorizer(ngram_range=(1, 2))),
    ('clf', LinearSVC(random_state=42, C=1.0, dual="auto"))
])

pipeline.fit(df['clean_text'], df['intent'])

# Evaluate on training data (just as initial sanity check)
print("Evaluating model...")
accuracy = pipeline.score(df['clean_text'], df['intent'])
print(f"Training Accuracy: {accuracy * 100:.2f}%")

# Save model
print("Saving model to model.pkl...")
with open('model.pkl', 'wb') as f:
    pickle.dump(pipeline, f)

print("Training complete!")

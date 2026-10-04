<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    // For Guru: Get materials for a specific class
    public function getGuruMaterials(Request $request, $tingkat)
    {
        $guru_id = $request->user()->id;
        
        $materials = Material::where('guru_id', $guru_id)
            ->where('tingkat', $tingkat)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['materials' => $materials]);
    }
    
    // For Guru: Create a new material
    public function storeGuru(Request $request)
    {
        $request->validate([
            'tingkat' => 'required|integer',
            'title' => 'required|string',
            'type' => 'required|string', // PDF, PPT, Video, Link, dll
            'file_url' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,jpg,jpeg,png,mp4,zip,rar'
        ]);
        
        $guru = $request->user();
        $subject_name = 'Pelajaran';
        if ($guru->jenjang_guru === 'smp' && $guru->subjectSmp) {
            $subject_name = $guru->subjectSmp->subject_name;
        } else if ($guru->jenjang_guru === 'sma' && $guru->subjectSma) {
            $subject_name = $guru->subjectSma->subject_name;
        } else if ($guru->subject) {
            $subject_name = $guru->subject->subject_name;
        }
        
        $fileUrl = $request->file_url;
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // Gunakan nama file asli ditambah timestamp agar tidak bentrok
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . str_replace(' ', '_', $originalName);
            
            $path = $file->storeAs('materials', $filename, 'public');
            $fileUrl = asset('storage/' . $path);
        }
        
        $material = Material::create([
            'guru_id' => $guru->id,
            'tingkat' => $request->tingkat,
            'subject_name' => $subject_name,
            'title' => $request->title,
            'type' => $request->type,
            'file_url' => $fileUrl
        ]);
        
        return response()->json([
            'message' => 'Materi berhasil ditambahkan',
            'material' => $material
        ]);
    }
    
    // For Siswa: Get materials matching their enrolled class
    public function getSiswaMaterials(Request $request)
    {
        $user = $request->user();
        
        // Find student's profile to get their enrolled class (tingkat)
        $profile = \App\Models\PendaftaranProfile::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['materials' => []]);
        }
        
        $tingkat = $profile->kelas_yang_didaftar;
        
        // Get all materials for this tingkat
        $materials = Material::where('tingkat', $tingkat)
            ->with('guru:id,name')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['materials' => $materials]);
    }
}

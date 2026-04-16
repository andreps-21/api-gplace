<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TermController extends Controller
{
    public function create()
    {
        return view('terms.create');
    }

    public function store(Request $request)
    {
        if ($request->hasFile('catalog') && $request->file('catalog')->isValid()) {

            Storage::disk('public')->delete('pdf/termos-condicoes.pdf');

            $uploads = $request->catalog->storeAs('pdf', 'termos-condicoes.pdf', 'public');

            settings()->put('terms', $uploads);
        }

        if ($request->hasFile('privacy') && $request->file('privacy')->isValid()) {

            Storage::disk('public')->delete('pdf/politicas-privacidade.pdf');

            $uploads = $request->privacy->storeAs('pdf', 'politicas-privacidade.pdf', 'public');

            settings()->put('privacy', $uploads);
        }

        return redirect()->route('terms.create')
            ->withStatus('Registro adicionado com sucesso.');
    }
}

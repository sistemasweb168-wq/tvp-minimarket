<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use Barryvdh\DomPDF\Facade\Pdf;

class ManualController extends Controller
{
    public function index()
    {
        $empresa = Empresa::first();
        return view('manual.index', compact('empresa'));
    }

    public function pdf()
    {
        $empresa = Empresa::first();
        
        $pdf = Pdf::loadView('manual.pdf', compact('empresa'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->download('Manual_Usuario_Sistema_Minimarket_Licoreria.pdf');
    }
}

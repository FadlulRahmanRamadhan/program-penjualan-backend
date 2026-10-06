<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BarangImport implements WithMultipleSheets
{
    protected $kategoriId;
    protected $merkId;
    protected $satuanId;
    protected $sheetName;

  public function __construct(
    $kategoriId,
    $merkId,
    $satuanId,
    $sheetName

) 



{
    $this->kategoriId = $kategoriId;
    $this->merkId = $merkId;
    $this->satuanId = $satuanId;
    $this->sheetName = $sheetName;
}

public function sheets(): array
{
    return [
        $this->sheetName => new BarangSheetImport(
            $this->kategoriId,
            $this->merkId,
            $this->satuanId
        ),
    ];
}
    
}
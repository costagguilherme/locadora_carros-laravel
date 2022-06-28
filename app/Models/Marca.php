<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;
    protected $table = 'marcas'; // tabela que deve ser mapeada pelo model
    protected $fillable = ['nome', 'imagem'];


    public function rules () {
        return [
            'nome' => 'required|unique:marcas,nome,'.$this->id, //unique:nome_tabela,nome_coluna, id a ser ignorado
            'imagem' => 'required|file|mimes:png'
        ];

    }

    public function feedback () {
        return [
            'required' => 'O campo :attribute é obrigatório',
            'nome.unique' => 'O nome da marca já existe',
            'imagem.mimes' => 'Arquivo dever ser .png'
        ];
    }

    public function modelos () {
        // Uma marca possui muitos modelos
        return $this->hasMany('App\Models\Modelo');

    }
}
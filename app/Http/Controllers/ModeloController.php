<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Modelo;
use App\Repositories\ModeloRepository;
use Illuminate\Http\Request;

class ModeloController extends Controller
{

    public function __construct(Modelo $modelo)
    {
        $this->modelo = $modelo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $modeloRepository = new ModeloRepository($this->modelo);
        if ($request->has('atributos_marca')) {
            $atributos_marca = 'marca:id,'.$request->atributos_marca ;
            $modeloRepository->selectAtributosDeRegistrosRelacionados($atributos_marca);

        } else {
            $modeloRepository->selectAtributosDeRegistrosRelacionados('marca');        
        }

        if ($request->has('filtro')) {
            $modeloRepository->filtro($request->filtro);
        }

        if ($request->has('atributos')) {
            $modeloRepository->selectAtributos($request->atributos);
        }

        return response()->json($modeloRepository->getResultado(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->modelo->rules());
        
        $imagem = $request->imagem;
        $imagemPath = $imagem->store('imagens/modelos', 'public');

        $modelo = $this->modelo->create([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagemPath,
            'numero_portas' => $request->numero_portas,
            'lugares' => $request->lugares,
            'air_bag' => $request->air_bag,
            'abs' => $request->abs
        ]);

        return response()->json($modelo, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);
        if ($modelo === null)  {
            return response()->json(['message' => 'Modelo does not exists'], 404);
        }
        
        return response()->json($modelo, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function edit(Modelo $modelo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // accept: application/json
        $modelo = $this->modelo->find($id);

        if ($modelo === null)  {
            return response()->json(['message' => 'Modelo does not exists'], 404);
        }

        $new_rules = [];
        if ($request->method() === 'PATCH') {

            foreach($modelo->rules() as $input => $rule) {
                if (array_key_exists($input, $request->all())) {
                    $new_rules[$input] = $rule;
                }
            }

            $request->validate($new_rules);

        } else if ($request->method() === 'PUT') {

            $request->validate($modelo->rules());

        }

        $imagemPath = $modelo->imagem;
        if ($request->file('imagem')) {
            Storage::disk('public')->delete($modelo->imagem);
            $imagem = $request->imagem;
            $imagemPath = $imagem->store('imagens/modelos', 'public'); // storage/app/public/imagens
        }

        $modelo->fill($request->all());
        $modelo->imagem = $imagemPath;

        $modelo->save();
        /*
        $modelo->update([
            'marca_id' => $modelo->marca_id,
            'nome' => $modelo->nome,
            'imagem' => $imagemPath,
            'numero_portas' => $modelo->numero_portas,
            'lugares' => $modelo->lugares,
            'air_bag' => $modelo->air_bag,
            'abs' => $modelo->abs
        ]);
        */
        return response()->json($modelo, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);
        if ($modelo === null)  {
            return response()->json(['message' => 'Modelo does not exists'], 404);
        }

        Storage::disk('public')->delete($modelo->imagem);
        
        $modelo->delete();
        return response()->json(['message' => 'Modelo deletado'], 200);
    }
}

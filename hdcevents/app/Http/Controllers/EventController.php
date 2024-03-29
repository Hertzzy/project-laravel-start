<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\User;

class EventController extends Controller
{
    public function index() {

        $search =  request('search');

        if($search){
            $events = Event::where([
                ['title', 'like', '%'.$search.'%']
            ])->get();
        }else {
            // Todos os eventos do banco de dados
            $events = Event::all();
        }

        return view('welcome', ['events' => $events, 'search' => $search]);
    }

    public function create() {
        return view('events.create');
    }

    public function contact() {
        return view('contact.contact');
    }

    public function store(Request $request) {

        // estancia a classe do model
        $event = new Event;

        $event->title = $request->title;
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;

        // Image Upload
        if($request->hasFile('image') && $request->file('image')->isValid()){

            $requestImage = $request->image;
            
            $extension = $requestImage->extension();

            // Nome da imagem no servidor
            $imageName = md5($requestImage->getClientOriginalName() . strtotime("now") . "." . $extension);

            // Salvar imagem
            $requestImage->move(public_path('img/events'), $imageName);

            $event->image = $imageName;

        }

        $user = auth()->user();
        $event->user_id = $user->id;
        // Salva no banco de dados
        $event->save();

        // Redireciona o usuario para outra página
        return redirect('/')->with('msg', 'Evento criado com sucesso!');
    }

    public function show($id){

        $event = Event::findOrFail($id);

        $eventOwner = User::where('id', $event->user_id)->first()->toArray();

        return view('events.show', ['event' => $event, 'eventOwner' => $eventOwner]);

    }

    public function dashboard() {

        $user = auth()->user();

        $events = $user->events;

        // $eventsAsParticipant = $events->eventsAsParticipant;

        return view('events.dashboard', ['events' => $events, ]);
    }

    public function destroy($id) {
        
        Event::findOrFail($id)->delete();

        return redirect('/dashboard')->with('msg', 'Evento excluído com sucesso!');
    }

    public function edit ($id) {

        $event = Event::findOrFail($id);

        return view('events.edit', ['event' => $event]);
    }

    public function update(Request $request) {

        Event::findOrFail($request-> id)->update($request->all());

        return redirect('/dashboard')->with('msg', 'Evento editado com sucesso!');
    }

    public function joinEvent($id) {

        $user = auth()->user();

        $user->eventsAsParticipant()->attach($id);

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Sua presença está confirmada' . $event->title);

    }
}

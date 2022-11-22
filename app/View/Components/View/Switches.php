<?php

namespace App\View\Components\View;

use Illuminate\View\Component;

class Switches extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $switches = collect([
            ["name" => "Switch 1", "id" => 1],
            ["name" => "Switch 2", "id" => 2],
            ["name" => "Switch 3", "id" => 3],
            ["name" => "Switch 4", "id" => 4],
            ["name" => "Switch 5", "id" => 5],
            ["name" => "Switch 6", "id" => 6],
        ]);
        return view('components.view.switches', compact('switches'));
    }
}

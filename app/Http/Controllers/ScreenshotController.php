<?php

namespace App\Http\Controllers;

use App\Models\Screenshot;
use App\Models\Task;
use Illuminate\Http\Request;

class ScreenshotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'screenshot' => 'required'
        ]);

        $screenshotPath = $request->file('screenshot')->store('screenshots');

        $task = Task::find($request->task_id);

        $task->screenshots()->create([
            'path' => $screenshotPath
        ]);
        
        $task->increaseTimeSpent(2 * 60);
        
        return response()->noContent();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Screenshot  $screenshot
     * @return \Illuminate\Http\Response
     */
    public function show(Screenshot $screenshot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Screenshot  $screenshot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Screenshot $screenshot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Screenshot  $screenshot
     * @return \Illuminate\Http\Response
     */
    public function destroy(Screenshot $screenshot)
    {
        //
    }
}

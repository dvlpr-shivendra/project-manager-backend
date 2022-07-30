<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use App\Models\Screenshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'screenshot' => 'required', Rule::dimensions()->maxWidth(1920)->maxHeight(1080),
        ]);

        $screenshotPath = $request->file('screenshot')->store('screenshots');

        $progress = Progress::where('task_id', $request->task_id)
            ->whereDate('created_at', Carbon::today())->first();

        if (!$progress) {
            $progress = Progress::create([
                'task_id' => $request->task_id,
                'user_id' => $request->user()->id,
            ]);
        }

        $progress->screenshots()->create([
            'path' => $screenshotPath
        ]);

        $progress->increaseDuration(2 * 60);

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

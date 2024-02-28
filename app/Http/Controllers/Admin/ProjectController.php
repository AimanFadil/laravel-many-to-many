<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Type;
use App\Models\Technology;




class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $projects = Project::all();


        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $types = Type::all();
        $technologies = Technology::all();
        return view('admin.projects.create', compact('types', 'technologies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProjectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {
        $form_data = $request->all();
        $project = new Project();

        if ($request->hasFile('emulazione')) {
            $path = Storage::disk('public')->put('projects_image', $form_data['emulazione']);
            $form_data['emulazione'] = $path;
        }


        $project->fill($form_data);
        $slug = Str::slug($form_data['nome'], '-');
        $project->slug = $slug;

        $project->save();
        /* dd($request->all()); */
        if ($request->has('technology')) {
            $project->technologies()->attach($form_data['technology']);;
        }

        return redirect()->route('admin.projects.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, Technology $technology)
    {
        return view('admin.projects.show', compact('project', 'technology'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $types = Type::all();
        $technologies = Technology::all();
        return view('admin.projects.edit', compact('project', 'types', 'technologies'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProjectRequest  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {


        $form_data = $request->all();

        if ($request->hasFile('emulazione')) {

            if ($project->emulazione != null) {
                Storage::disk('public')->delete($project->emulazione);
            }
            $path = Storage::disk('public')->put('projects_image', $form_data['emulazione']);
            $form_data['emulazione'] = $path;
        }
        $slug = Str::slug($form_data['nome'], '-');
        $form_data['slug'] = $slug;
        $project->update($form_data);

        if ($request->has('technology')) {
            $project->technologies()->sync($form_data['technology']);
        } else {
            $project->technologies()->sync([]);
        }

        return redirect()->route('admin.projects.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        if ($project->emulazione != null) {
            Storage::disk('public')->delete($project->emulazione);
        }
        $project->delete();
        return redirect()->route('admin.projects.index');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Task;
use App\User;
use App\TaskNote;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('user_id')) {
          $user_id = $request->get('user_id');
        } else if (!$request->has('search_in')) {
          $user_id = auth()->user()->id;
        } else {
          $user_id = 'ALL';
        }
        
        if (!$request->has('task_id')) {
          
          $array = null;
          
          if (!$request->has('id')) {
            $ids = Task::findTaskable($request->get('search_for'), $request->get('search_in'));            
          } else {
            $ids = [$request->get('id')];
          }
          
          if (count($ids) == 1) {
            $array = Task::getTaskable($ids[0], $request->get('search_in'));
          } 
          
          $tasks = Task::with('taskable', 'assigned_user', 'create_user', 'notes')
                          ->searchStatus($request->get('status'))
                          ->searchTaskable($ids, $request->get('search_in'))
                          ->where('is_deleted', '0')
                          ->searchUser($user_id)
                          ->searchCreator($request->get('create_user_id'))
                          ->orderBy('status')
                          ->orderBy('created_at', 'ASC')
                          ->get();
        } else {
          $tasks = Task::with('taskable', 'assigned_user', 'create_user', 'notes')
                          ->where('id', $request->get('task_id'))
                          ->get(); 
          
          $array = Task::getTaskable($tasks->first()->taskable_id, $tasks->first()->taskable_type);
        }
        
        foreach ($tasks as $task) {
          
          if ($task->msg_read == '0' && $task->assigned_user_id == auth()->user()->id) {
            $task->msg_read = '1';
            $task->save();
          } else if ($task->msg_read == '1') {
            $task->msg_read = '2';
            $task->save();
          }
        }
        
        $users = User::where('is_deleted', '0')
                      ->orderBy('username')
                      ->get()
                      ->pluck('username', 'id')
                      ->prepend('', 'ALL');
                      
        $statuses = ['O' => 'Open', 'C' => 'Closed', 'ALL' => 'All'];
        
        $tables = Task::getTables();
        
        return view ( 'tasks.index', compact ( 'tasks', 'users', 'user_id', 'array', 'statuses', 'tables', 'request' ) );
    }
    
    public function create()
    {
      
    }

    public function store(Request $request)
    {   
        if ($request->get('user') == 'ALL') {
          return redirect()->back()->withErrors('Please select a user for this task');
        }
        
        if (!$request->has('id')) {
          $ids = Task::findTaskable($request->get('associate_with'), $request->get('model'));
          
          if (count($ids) == 1) {
            $id = $ids[0];
            $model = $request->get('model');
          } else {
            $id = null;
            $model = null;
          }
        } else {
          $id = $request->get('id');
          $model = $request->get('model');
        }
        
        $task = Task::new($request->get('text'), 
                          $request->get('user'), 
                          $model, 
                          $id, 
                          $request->get('close_event'), 
                          $request->get('previous_task'),
                          null,
                          $request->get('due_date')
                        );
        if ($task) {
          $this->save_attachment($task->id, $request->file('attach'));
        }
        
        return redirect()->action('TaskController@index', ['task_id' => $task->id]);
    }
    
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {   
        $task = Task::find($id);
        
        if (!$task) {
          return;
        }
        
        $note = null;
        
        if ($request->has('note_text')) {
          $note = new TaskNote;
          $note->task_id = $id;
          $note->text = $request->get('note_text');
          $note->user_id = auth()->user()->id;
          $note->save();
          
          $task->msg_read = '0';
          $task->save();
        }
        
        $saved = $this->save_attachment($id, $request->file('attach'));
        
        if ($saved) {
          $task->msg_read = '0';
          $task->save();
        }
        
        if ($request->has('reassign') && $request->get('reassign') != 'ALL') {
          
          if ($request->get('reassign') != $task->assigned_user_id) {
            
            $new_user = User::find($request->get('reassign'));
            
            $note_text = '  ( Reassigned to '  . $new_user->username . ' )';
            
            if (!$note) {
              $note = new TaskNote;
              $note->task_id = $id;
              $note->user_id = auth()->user()->id;
            }
            
            $note->text .= $note_text;
            $note->save();
            
            $task->assigned_user_id = $request->get('reassign');
            $task->msg_read = '0';
            $task->save();
            
          }
        }
        
        return redirect()->action('TaskController@index', ['task_id' => $task->id]);
    }
    
    private function save_attachment($id, $file) {
      
      if ($file == null) {
        return false;
      }
      
      $filename = $id . date("_Ymd_His.", strtotime('now')) . $file->getClientOriginalExtension();
			
			if(move_uploaded_file($file, base_path() . '/public_html/assets/attachments/' . $filename)) {
        $note = new TaskNote;
        $note->task_id = $id;
        $note->text = $filename;
        $note->ext = strtolower($file->getClientOriginalExtension());
        $note->user_id = auth()->user()->id;
        $note->save();
        
        return true;;
      } 
      
      return false;
    }
    
    public function destroy($id)
    {
        $task = Task::find($id);
        
        if ($task) {
          $task->status = 'C';
          $task->save();
          
          $note = new TaskNote;
          $note->task_id = $id;
          $note->text = 'Task Closed';
          $note->user_id = auth()->user()->id;
          $note->save();
          
          return redirect()->back()->withSuccess('Task Closed');
          
        } else {
          return redirect()->back()->withError('Task Not Found');
        }
    }
    
    public function tasksDue ()
    {
      $tasks = Task::where('status', 'O')
                      ->where('due_date', '<=', date("Y-m-d"))
                      ->update(['msg_read' => '0']);
      
      return;
    }
}

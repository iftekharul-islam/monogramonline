<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Tasks</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
		
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	
	<style>
	.remove_button_css { 
		outline: none;
		padding: 0px; 
		border: 0px; 
		box-sizing: none; 
		background-color: transparent;
	}
	
	.note-bubble {
		position: relative;
		/* background: #dff9fb; */
		border-radius: .4em;
		margin-bottom: 10px;
		padding: 5px;
	}
	
	img.Image { max-width: 100%;}
	
	tr.toggle td {
	  border-bottom:2pt solid grey;
	}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="width:95%;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Tasks</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">
						Search Tasks 
						<button type="button" class="btn btn-info btn-xs pull-right"
										data-toggle="collapse" data-target="#create_panel">New Task</button>
				</div>
				<div class = "panel-body">
				{!! Form::open(['method' => 'get']) !!}
										
				<div class = "col-xs-12">

					<div class="form-group">
						
						<div class = "form-group col-xs-2">
							<label>Task Status</label>
							{!! Form::select('status', $statuses, $request->get('status'), ['id'=>'status', 'class' => 'form-control chosen_txt']) !!}
						</div>
						
						<div class = "form-group col-xs-2">
							<label>Assigned To</label>
							{!! Form::select('user_id', $users, $user_id, ['id'=>'user_id', 'class' => 'form-control']) !!}
						</div>
						
						<div class = "form-group col-xs-2">
							<label>Created By</label>
							{!! Form::select('create_user_id', $users, $request->get('create_user_id'), ['id'=>'create_user_id', 'class' => 'form-control']) !!}
						</div>
						
						<div class = "form-group col-xs-2">
							<label>Search For</label>
							{!! Form::text('search_for', $request->get('search_for'), ['id'=>'search_for', 'class' => 'form-control']) !!}
						</div>
						
						<div class = "form-group col-xs-2">
							<label>Search In</label>
							{!! Form::select('search_in', $tables, $request->get('search_in'), ['id'=>'search_in', 'class' => 'form-control']) !!}
						</div>
						
						<div class = "form-group col-xs-2">
							{!! Form::submit('Filter', ['id'=>'filter', 'style' => 'margin-top: 20px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
						</div>
					</div>
				</div>
			{!! Form::close() !!}
			</div>
		</div>

		<div class = "col-xs-12">
			<div class = "panel panel-info @if(count($tasks) > 0) collapse @endif" id="create_panel">
				<div class = "panel-heading">Create Task</div>
				<div class = "panel-body">
					{!! Form::open(['method' => 'POST', 'url' => url('/tasks'), 'id' => 'create_form', 'files'=>'true']) !!}
						<div class = "form-group col-xs-2">
							{!! Form::label('For:', '') !!}
							{!! Form::select('user', $users, null, ['id'=>'user', 'class' => 'form-control']) !!}
							<br>
							{!! Form::label('Due:', '') !!}
							<div class = 'input-group date' id = 'due_date_picker'>
								{!! Form::text('due_date', $request->get('due_date'), ['id'=>'due_datepicker', 'class' => 'form-control', 'placeholder' => '(optional)', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-5">
							{!! Form::label('Task:', '') !!}
							{!! Form::textarea('text', null, ['id'=>'create_text', 'rows' => '1', 'class' => 'form-control']) !!}
							<br><br>
							{!! Form::file('attach', ['style' => 'margin-top:7px;', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-3" style="border:1px solid #E0DDD7;">
							{!! Form::label('Associate with:', '') !!}
							<br>
							@if ($array)
								{!! App\Task::outputTaskable($array) !!}
								{!! Form::hidden('model', $array[0]) !!}
								{!! Form::hidden('id', $array[1]) !!}
								<br><br>
							@else
								{!! Form::select('model', $tables, '', ['class' => 'form-control']) !!}
								<br>
								{!! Form::text('associate_with', '', ['class' => 'form-control', 'placeholder' => 'Search For']) !!}
								<br><br>
							@endif
						</div>
						<div class = "form-group col-xs-1">
							{!! Form::label('', '') !!}
							{!! Form::submit('Create', ['class' => 'btn btn-info']) !!}
						</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
		
		</div>
		<div class = "col-xs-12">
		
		@if(count($tasks))
			
			<table class="table table-condensed" width="95%">
					
					@foreach($tasks as $task)
						<tr @if ($task->status == 'C') bgcolor="#F0F0F0" @endif>
							<td width=10>
								@if ($task->status == 'O')
									<a onclick='$("#note-{{ $task->id }}").toggle();'><i class = 'glyphicon glyphicon-pencil'></i></a>
								@endif
							</td>
							<td width=10>
								@if (auth()->user()->id == $task->assigned_user_id && $task->status == 'O')
									{!! Form::open(['method' => 'DELETE', 'url' => url('/tasks/' . $task->id), 'id' => 'close_form']) !!}
									<button type="submit" class="remove_button_css">
									   <span  data-toggle = "tooltip" data-placement = "top" title = "Done" 
										 				class="glyphicon glyphicon-ok text-success"></span>
									</button>
									{!! Form::close() !!}
								@endif
							</td>
							<td width=100>
								@if ($task->msg_read != '2')
									<strong>*Task {{ $task->id }}</strong>
								@else 
									Task {{ $task->id }}
								@endif
								
								@if ($task->due_date && $task->due_date != '0000-00-00' && $task->due_date <= date("Y-m-d"))
									<br>
									<strong class="text-danger blink_me">Due {{ substr($task->due_date, 5) }}</strong>
								@elseif ($task->due_date && $task->due_date != '0000-00-00')
									<br>Due {{ substr($task->due_date, 5) }}
								@endif
							</td>
							<td width=100>
								@if (!$task->assigned_user)
									USER NOT FOUND
								@elseif($task->status == 'O')	
									<strong>{{ $task->assigned_user->username }}</strong>
								@elseif ($task->status == 'C')
									{{ $task->assigned_user->username }}
								@else
									Unrecognized status
								@endif
							</td>
						
							<td width=500>
								<div class="note-bubble alert-info">
								{{ $task->text }}
								<br>
								<small>
								@if ($task->create_user)
									- {{ $task->create_user->username }}
								@else
									USER NOT FOUND
								@endif
								{{ $task->created_at }}
								</small>
								</div>
								
								@foreach($task->notes as $note)
										@if (!isset($class) || $class != 'alert-warning')
											@setvar($class = 'alert-warning')
										@else
											@setvar($class = 'alert-success')
										@endif
										<div class="note-bubble {{ $class }}">
											@if ($note->ext == null)
												{{ $note->text }}
											@else
												<a download="{{ $note->text }}" href="/assets/attachments/{{ $note->text }}">
												@if (in_array($note->ext, ['gif','jpg','png','jpeg']))
														<img src="/assets/attachments/{{ $note->text }}" class="Image">
												@else
													Download {{ $note->ext }}
												@endif
												</a>
											@endif
											<br>
											<small>
											@if ($note->user)
												- {{ $note->user->username }}
											@else
											 	USER NOT FOUND
											@endif
											{{ $note->created_at }}
											</small>
										</div>
								@endforeach
							</td>
							
							<td colspan=2 width=200>
							@if ($task->taskable)
								{!! App\Task::outputTaskable($task->taskable) !!}
							@endif
							</td>
						</tr>
						
						<tr id="note-{{ $task->id }}" class="toggle info" style="display:none;">
							{!! Form::open(['method' => 'PUT', 'url' => url('/tasks/' . $task->id), 'id' => 'close_form', 'files'=>'true']) !!}
							@if($task->assigned_user_id == auth()->user()->id || auth()->user()->accesses->where('page', 'supervisor')->all())
								<td colspan="2" align="right">
									{!! Form::label('Reassign:', '', ['style' => 'margin-top:5px;']) !!}
								</td>
								<td colspan="2">
									{!! Form::select('reassign', $users, null, ['id'=>'reassign', 'class' => 'form-control']) !!}
								</td>
							@else
								<td colspan=4></td>
							@endif
							<td>
								{!! Form::textarea('note_text', null, ['id'=>'note_text', 'rows' => '2', 'class' => 'form-control', 'placeholder' => 'Add Note']) !!}
							</td>
							<td>
								{!! Form::file('attach', ['class' => 'form-control']) !!}
							</td>
							<td>
								{!! Form::submit('Update Task ' . $task->id, ['id'=>'note_button', 'class' => 'btn btn-primary']) !!}
							</td>
							{!! Form::close() !!}
						</tr>
					@endforeach
			</table>
		
    @else
        <div class = "alert alert-warning text-center">
          No Tasks found.
        </div>
    @endif
		
		</div>
	</div>
	
	<script>
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});

		var picker = new Pikaday(
		{
				field: document.getElementById('due_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date(),    
		});

	</script>
</body>
</html>
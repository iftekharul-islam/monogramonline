<div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="upload-modal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">Upload a Graphic
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
        </h4>
      </div>
      {!! Form::open(['name' => 'track', 'url' => '/graphics/upload_file', 'method' => 'post', 'files'=>'true']) !!}
      {!! Form::hidden('item_id', '', ['id' => 'upload_item_id']) !!}
      {!! Form::hidden('batch_number', '', ['id' => 'upload_batch_number']) !!}
      <div class="modal-body">
        <p>Upload a graphic to the Archive directory.</p>
        {!! Form::file('upload_file', ['class' => 'form-control']) !!}
      </div>
      <div class="modal-footer">
        {!! Form::submit('Upload', ['class' => 'btn btn-success']) !!}
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</div>

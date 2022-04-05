@extends('adminlte::page')

@section('title', 'CRUD API')

@section('content')
	
	@if(session('error'))	
    @include('layouts.error', ['error' =>  session('error')])
	@endif
	
	@if(session('success'))	
    @include('layouts.success', ['success' =>  session('success')])
	@endif	
	
	<div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">Create New Fluxo</div>
                    <div class="card-body">
                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <form method="POST" action="{{ route('laracase.crud.blade.storage') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            @include ('laracase::crud.form.form', ['formMode' => 'create'])
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
	@include ('laracase::crud.grid.modalSelect')
	<!--
	<div id="meuModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-xl">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Model</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
			<form>
			  <div class="form-group">
				<label for="recipient-name" class="col-form-label">Modelo:</label>
				<input type="text" class="form-control" id="model_name" name="model_name">
			  </div>
			  <div class="form-group">
				<label for="message-text" class="col-form-label">Id:</label>
				<input type="text" class="form-control" id="model_id" name="model_id">
			  </div>
			  <div class="form-group">
				<label for="message-text" class="col-form-label">Desc:</label>
				<input type="text" class="form-control" id="model_ds" name="model_ds">
			  </div>
			</form>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
			<button type="button" class="btn btn-primary" id="btn_save_model">Enviar</button>
		  </div>
		</div>
	  </div>
	</div>
-->
@endsection

@section('adminlte_js')
<script>
var fieldName = null;

function changeType(e, field){
	//console.debug(e);
	//alert('mudando o tipo de dados');
	//elType = e;
	if(e.value == 'select'){
		$('#meuModal').modal();
		//fieldName = field;
		fieldName = e.getAttribute('data-field');
	}
}	


$(document).ready(function(){
	
	$('#btn_save_model').click(function(){	
		var el = document.querySelector("#option_"+fieldName+" > input[type=hidden]");
		console.debug(el);
		el.value = $('#model_name').val()+':'+$('#model_id').val()+"@@"+$('#model_ds').val();
		$('#model_name').val('');
		$('#model_id').val('');
		$('#model_ds').val('');
		$('#meuModal').modal('hide');
	});
	
	$('#fld_tabela').change(function(){
		$.ajax({
		  url: "/laracase/crud/create/"+$(this).val()+'/ajax?modulo=form',
		  dataType: 'html',
		  beforeSend: function( xhr ) {
			//xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
		  }
		}).done(function( data ) {
			$('#grid_fields').html(data);
			
			$('select.fld-type').bind("change",function(){
				// vai adicionar conteudo a coluna options da linha
				var field = $(this).attr('data-field');
				//alert(field);
				if( $(this).val() == 'select'){
					
				}
				
			});
					
		});
		
	});
	

});
</script>
@endsection

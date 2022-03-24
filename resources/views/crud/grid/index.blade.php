@extends('adminlte::page')

@section('title', 'CRUD :: GRID DataTables')

@section('content_header')
    <h1>CRUD :: GRID DataTables</h1>
@stop

@section('content')
	
	
	<div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
						<h3 class="card-title">Create New Grid Datatable</h3>
					</div>
                    <div class="card-body">
                        <br />

                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <form method="POST" action="{{ url('/crud/createGrid') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include ('laracase::crud.grid.form', ['formMode' => 'grid'])

                        </form>

                    </div>
                </div>
            </div>
        </div>
	</div>
	@include ('laracase::crud.grid.modalSelect')

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
		var el = document.querySelector("#option_"+fieldName+" > input[type=text]");
		console.debug(el);
		el.value = $('#model_name').val()+':'+$('#model_id').val()+"@@"+$('#model_ds').val();
		$('#model_name').val('');
		$('#model_id').val('');
		$('#model_ds').val('');
		$('#meuModal').modal('hide');
	});
	
	$('#fld_tabela').change(function(){
		$.ajax({
		  url: "/crud/create/"+$(this).val()+'/ajax?modulo=grid',
		  dataType: 'html',
		  beforeSend: function( xhr ) {
			//xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
		  }
		}).done(function( data ) {
			//console.debug( data );
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

@if($formMode === 'edit')
<div class="form-group {{ $errors->has('id_fluxo') ? 'has-error' : ''}}">
    <label for="id_fluxo" class="control-label">{{ 'Id' }}</label>
    <input class="form-control" name="id_fluxo" type="number" id="id_fluxo" value="{{ isset($fluxo->id_fluxo) ? $fluxo->id_fluxo : ''}}" >
    {!! $errors->first('id_fluxo', '<p class="help-block">:message</p>') !!}
</div>
@endif
<div class="row">
	<div class="col-sm-4">
		<div class="form-group">
			<label for="tabela" class="control-label">Tabela</label>
			<div class="select">
				<select name="tabela" id="fld_tabela" required class="form-control">
					<option value=""></option>
				   @foreach($tabelas as $tabela)
				   <option value="{{ trim($tabela->$column) }}" @if ( trim(old('tabela')) == trim($tabela->$column)) selected @endif>{{ $tabela->$column}}</option>
				   @endforeach
				</select>
			</div>
		</div>	
	
	</div>
	<div class="col-sm-4">
		<div class="form-group {{ $errors->has('group') ? 'has-error' : ''}}">
			<label for="model" class="control-label">Namespace</label>
			<input class="form-control" name="group" type="text" id="group" value="{{old('group')}}"required>
			{!! $errors->first('group', '<p class="help-block">:message</p>') !!}
		</div>
	</div>
	<div class="col-sm-4">
		<div class="form-group {{ $errors->has('model') ? 'has-error' : ''}}">
			<label for="model" class="control-label">Nome Crud</label>
			<input class="form-control" name="model" type="text" id="model" value="{{old('model')}}"required>
			{!! $errors->first('model', '<p class="help-block">:message</p>') !!}
		</div>
	</div>
</div>
<div class="row">	
	<div class="col-sm-1">
		<div class="form-group {{ $errors->has('auth') ? 'has-error' : ''}}">
			<div class="checkbox">
				<label><input name="auth" type="checkbox" value="yes" @if ( old('auth') == 'yes') checked @endif> Auth</label>
			</div>
			{!! $errors->first('auth', '<p class="help-block">:message</p>') !!}
		</div>
	</div>
	<div class="col-sm-1">
		<div class="form-group {{ $errors->has('tabs') ? 'has-error' : ''}}">
			<div class="checkbox">
				<label><input name="tabs" type="checkbox" value="yes" @if ( old('tabs') == 'yes') checked @endif> Tabs</label>
			</div>
			{!! $errors->first('tabs', '<p class="help-block">:message</p>') !!}
		</div>
	</div>

	@if($formMode === 'grid')
	<div class="col-sm-1">
		<div class="form-group {{ $errors->has('button') ? 'has-error' : ''}}">
			<div class="checkbox">
				<label><input name="button" type="checkbox" value="S" @if ( old('button') == 'S') checked @endif> Botões</label>
			</div>
			{!! $errors->first('button', '<p class="help-block">:message</p>') !!}
		</div>
	</div>
	@endif
</div>

<table class="table" id="grid_fields">

	@if(!empty($ajax)) {!! $ajax !!}
	@else
	<tr>
		<th>Campo</th>
		<th>Label</th>
		<th>Tipo</th>
		<th>Obrigatório</th>
	</tr>		
	@endif
</table>	
<div class="form-group">
    <input class="btn btn-primary" type="submit" value="{{ $formMode === 'edit' ? 'Update' : 'Create' }}">
</div>
<script>
</script>
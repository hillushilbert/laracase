	@if(old('label.'.$field) !== null)		
		@php
		$required = old('required.'.$field);
		$readonly = old('readonly.'.$field);
		$option = old('option.'.$field);
		$gridFields = old('gridFields');
		$type = old('type.'.$field);
		@endphp
	@else
		$gridFields = [];	
	@endif
	<tr>
		<td><input type="checkbox" name="field[]" value="{{$field}}" checked> {{$field}}</td>
		<td><input type="text" name="label[{{$field}}]" value="@if(old('label.'.$field) !== null){{old('label.'.$field)}}@else{{$label}}@endif"></td>
		<td></td>
		<td>
		<select name="type[{{$field}}]" class="fld-type"  data-field="{{$field}}" onchange="changeType(this, '{{$field}}')">
		@foreach($types as $sType)
			<option value="{{$sType}}" @if($sType == $type) selected @endif>{{$sType}}</option>
		@endforeach
		</select>
		</td>
		<td>
			<input type="checkbox" name="required[{{$field}}]" value="S" @if($required == 'S') checked @endif><!-- Sim 
			<input type="radio" name="required[{{$field}}]" value="N" @if($required == 'N') checked @endif> Não-->
		</td>
		<td>
			<input type="checkbox" name="readonly[{{$field}}]" value="S" @if($readonly == 'S') checked @endif><!-- Sim
			<input type="radio" name="readonly[{{$field}}]" value="N" @if($readonly == 'N') checked @endif> Não-->
		</td>		
		<td>
			<select name="validation[{{$field}}][]">
				<option value=""></option>
				<option value="CPF">CPF</option>
				<option value="CNPJ">CNPJ</option>
				<option value="Email">E-Mail</option>
			</select>
		</td>		
		<td id="option_{{$field}}" class="fld-options" >
			<input type="text" name="option[{{$field}}]" style="width: 20px;" value="@if(old('option.'.$field) !== null){{ old('option.'.$field) }}@endif">
		</td>
		<td>
			<select name="tab_fields[{{$field}}]">
				<option value="0">Tab0</option>
				<option value="1">Tab1</option>
				<option value="2">Tab2</option>
				<option value="3">Tab3</option>
			</select>
		</td>
		<td><input type="checkbox" name="gridFields[]" value="{{$field}}" @if(!empty($gridFields) && in_array($field,$gridFields)) checked @endif></td>
		<td><input type="radio" name="groups[]" value="{{$field}}" @if(!empty($groups) && in_array($field,$groups)) checked @endif></td>
	</tr>
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
				<option value="CPF"  @if(!empty(old('validation.'.$field)) && in_array('CPF',old('validation.'.$field))) selected @endif>CPF</option>
				<option value="CNPJ"  @if(!empty(old('validation.'.$field)) && in_array('CNPJ',old('validation.'.$field))) selected @endif>CNPJ</option>
				<option value="Email"  @if(!empty(old('validation.'.$field)) && in_array('Email',old('validation.'.$field))) selected @endif>E-Mail</option>
			</select>
		</td>		
		<td id="option_{{$field}}" class="fld-options" >
			<input type="hidden" name="option[{{$field}}]" value="@if(old('option.'.$field) !== null){{ old('option.'.$field) }}@endif">
		</td>
		<td>
			<select name="tab_fields[{{$field}}]">
				<option value="0" @if(old('tab_fields.'.$field) == "0") selected @endif>Tab0</option>
				<option value="1" @if(old('tab_fields.'.$field) == "1") selected @endif>Tab1</option>
				<option value="2" @if(old('tab_fields.'.$field) == "2") selected @endif>Tab2</option>
				<option value="3" @if(old('tab_fields.'.$field) == "3") selected @endif>Tab3</option>
			</select>
		</td>
		<td><input type="checkbox" name="gridFields[]" value="{{$field}}" @if(!empty($gridFields) && in_array($field,$gridFields)) checked @endif></td>
		<td><input type="checkbox" name="searchFields[]" value="{{$field}}" @if(!empty(old('searchFields')) && in_array($field,old('searchFields'))) checked @endif></td>
	</tr>
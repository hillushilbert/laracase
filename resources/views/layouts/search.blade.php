<form class="form-inline" action="{{ $rota }}" method="POST" role="search">
    {{ csrf_field() }}
    <input name="_method" type="hidden" value="GET">
    <input class="form-control mr-sm-2" type="search" name="busca" placeholder="Digite sua pesquisa" aria-label="Search" value="{{ request('busca') }}">
    <button class="btn btn-outline-primary my-2 my-sm-0" data-toggle="tooltip" data-placement="top" title="Efetuar pesquisa" type="submit"><i class="fas fa-search"></i></button>
</form>
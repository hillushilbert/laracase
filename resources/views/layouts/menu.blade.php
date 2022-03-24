        <nav class="navbar navbar-expand-md navbar-light navbar-laravel" style="background-color: #E0EEEE;">
            <div class="container-fluid">
                <!--<a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Middleware') }}
                </a>-->
                <a class="navbar-brand" href="/home">
                    <img src="{{ asset('images/logo-sin.png') }}" width="240" height="43" alt="">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('laracase.crud.new') }}">Novo Crud</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('laracase.crud.grid') }}">Nova Grid</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('laracase.crud.api') }}">Nova API</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdownCadastro" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <strong>Administração</strong> <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownCadastro">
                                    
                                    <a class="dropdown-item" href="{{ route('laracase.crud.new') }}">
                                        <i class="fas fa-flask"></i> Clinicas
                                    </a>
                                </div>
                            </li>    

                            @if(Auth::check())
							<li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fas fa-user"></i><strong> Usuário: {{ Auth::user()->name }}</strong> <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fas fa-door-open"></i> {{ __('Sair') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                            @endif
                    </ul>
                </div>
            </div>
        </nav>
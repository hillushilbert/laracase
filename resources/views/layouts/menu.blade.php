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
						@guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @else
{{--                        <li class="nav-item">--}}
{{--                            <a class="nav-link" href="{{ route('protocolo.create') }}">Protocolo</a>--}}
{{--                        </li>--}}
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('pedido') }}">Pedidos</a>
                            </li>
                            @if(in_array(Auth::user()->id_perfil, [1, 3]))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('nf.index') }}">Notas Fiscais</a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('estoque.index') }}">Estoque</a>
                            </li>
                            @if(Auth::user()->perfil->id == 1)
                            <li class="nav-item dropdown">
                                <a id="navbarDropdownCadastro" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <strong>Administração</strong> <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownCadastro">
                                    
                                    <a class="dropdown-item" href="{{ route('clinicas.index') }}">
                                        <i class="fas fa-flask"></i> Clinicas
                                    </a>

                                    <a class="dropdown-item" href="{{ route('medicos.index') }}">
                                        <i class="fas fa-flask"></i> Médicos
                                    </a>

                                    <a class="dropdown-item" href="{{ route('produtos.index') }}">
                                        <i class="fas fa-flask"></i> Produtos
                                    </a>
                                </div>
                            </li>    

							<li class="nav-item dropdown">
                                <a id="navbarDropdownCadastro" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <strong>Cadastros</strong> <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownCadastro">
                                    
                                    <a class="dropdown-item" href="{{ route('usuarios.index') }}">
                                        <i class="fas fa-flask"></i> Usuários
                                    </a>                                
                                    <a class="dropdown-item" href="{{ route('laboratorio') }}">
                                        <i class="fas fa-flask"></i> Laboratório
                                    </a>

									 <a class="dropdown-item" href="{{ route('distribuidor') }}">
                                        <i class="fas fa-building"></i> Distribuidor
                                    </a>
									 <a class="dropdown-item" href="{{ route('formaspagamento.index') }}">
                                        <i class="fas fa-building"></i> Formas de pagamento
                                    </a>	
									 <a class="dropdown-item" href="{{ route('motivorecusa.index') }}">
                                        <i class="fas fa-building"></i> Motivos Recusa
                                    </a>									
									
                                </div>
                            </li>

							<li class="nav-item dropdown">
                                <a id="navbarDropdownRelatorio" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <strong>Relatórios</strong> <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownRelatorio">
                                    <a class="dropdown-item" href="{{ route('test.report') }}">
                                        <i class="fas fa-flask"></i> TestSuite
                                    </a>

                                    <a class="dropdown-item" href="{{ route('report.pedidos.index') }}">
                                        <i class="fas fa-flask"></i> Pedidos Elastic Search
                                    </a>

                                    <a class="dropdown-item" href="{{ route('log') }}">
                                        <i class="fas fa-cash-register"></i> Log
                                    </a>
                                </div>
                            </li>
                            @endif
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

                            
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
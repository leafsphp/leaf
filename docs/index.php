<!doctype html>
<html>
    <head>
        <title>Vier API documentation</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="prism.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/styles/default.min.css">
        <style>
            body {
                width: 100%;
            }

            .vier-blue-bg {
                background: #23292f !important;
            }

            .hero {
                width: 100%;
                height: 400px;
                background: url("https://source.unsplash.com/random");
                background-size: cover !important;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                color: white;
            }

            .page-content {
                display: flex;
            }

            .collection {
                margin-top: 50px;
                width: 300px
            }

            .collection a {
                color: #23292f !important;
            }

            .main-content {
                margin-left: 20px;
            }

            pre {
                margin-left: -180px !important;
            }

            @media only screen and (max-width: 799px) {
                .main-content {
                    margin-left: 0px !important;
                }

                .collection {
                    display: none !important;
                }
            }
        </style>
    </head>
    <body style="width: 100%;">

        <div class="navbar-fixed">
            <nav>
                <div class="nav-wrapper vier-blue-bg">
                    <a href="#!" class="brand-logo center">Vier API</a>
                </div>
            </nav>
        </div>

        <div class="hero">
            <!-- <h3>Hello....</h3> -->
        </div>

        <div class="container">
            <div class="page-content">
                <aside>
                     <div class="collection" style="margin-left: -150px;">
                        <a href="#!" class="collection-item">Register</a>
                        <a href="#!" class="collection-item">Login</a>
                        <a href="#!" class="collection-item">Interwallet</a>
                        <a href="#!" class="collection-item">Send Cash</a>
                        <a href="#!" class="collection-item">Edit User</a>
                    </div>
                </aside>
                <div class="main-content">

                    <h2>Portal Online API</h2>
                    <h5>This is a PHP REST API that handles all operations for Portal Online.</h5>

                    <p>
                        <h5>Getting Started</h5>
                        All requests should be made to <code>/api/v1/</code>

                        <br>
                        <h5>API Usage & Endpoints</h5>
                        <hr>
                        <b>Register a User</b><br>
                        Registering a user will require special parameters in your request, for starters, your URL should be <code>[POST /api/vi/]</code><br><br>
                        <b>Add user and request JSON web token</b><br>

                        <b>Headers</b><br>
                        <code>Content-type: application/json</code><br><br>

                        <b>Body<b><br>
                        <pre>
                            <code class="json">
                        {
                            "name": "register",
                            "param": {
                                "name": "",
                                "email": "",
                                "pass": ""
                            }
                        }
                            </code>
                        </pre><br><br>

                        Response: &nbsp;<code>200 (application/json)</code><br><br>

                        <b>Body</b><br>

                        <pre>
                            <code class="json">
                        {
                            "status": 200,
                            "result": {
                                "token": "",
                                "user": {
                                    "id": "",
                                    "username": "",
                                    "email": "",
                                    "mobile_number": "",
                                    "balance": "",
                                    "mobile_balance": "",
                                    "crypto_balance": ""
                                }
                            }
                        }
                            </code>
                        </pre>

                        <br>
                        <br>
                        <hr>
                        <b>Login</b><br>
                        logging a user in will require special parameters in your request, for starters, your URL should be <code>[POST /api/vi/]</code><br><br>
                        <b>Login and request JSON web token</b><br>

                        <b>Headers</b><br>
                        <code>Content-type: application/json</code><br><br>

                        <b>Body<b><br>
                        <pre>
                            <code class="json">
                        {
                            "name": "login",
                            "param": {
                                "email": "",
                                "pass": ""
                            }
                        }
                            </code>
                        </pre><br><br>

                        Response: &nbsp;<code>200 (application/json)</code><br><br>

                        <b>Body</b><br>

                        <pre>
                            <code class="json">
                        {
                            "status": 200,
                            "result": {
                                "token": "",
                                "user": {
                                    "id": "",
                                    "username": "",
                                    "email": "",
                                    "mobile_number": "",
                                    "balance": "",
                                    "mobile_balance": "",
                                    "crypto_balance": ""
                                }
                            }
                        }
                            </code>
                        </pre>
                    </p>
                    <pre>

                </div>
            </div>
        </div>

        <footer class="page-footer vier-blue-bg">
          <div class="container">
            <div class="row">
              <div class="col l6 s12">
                <h5 class="white-text">Portal Network</h5>
                <p class="grey-text text-lighten-4">The future of online banking.</p>
              </div>
              <div class="col l4 offset-l2 s12">
                <h5 class="white-text">Portal Links</h5>
                <ul>
                  <li><a class="grey-text text-lighten-3" href="#">Portal Network</a></li>
                  <li><a class="grey-text text-lighten-3" href="#">Portal Online</a></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="footer-copyright">
            <div class="container">
            &copy; <script>document.write(new Date().getFullYear());</script> Portal Network
            <a class="grey-text text-lighten-4 right" href="#!">More Links</a>
            </div>
          </div>
        </footer>
        <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.10/highlight.min.js"></script>
    </body>
</body>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playwrite+IS:wght@100..400&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap"
        rel="stylesheet">
    <!-- bootstrap -->
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.4/components/logins/login-9/assets/css/login-9.css">
    <style>
        /* General Styling */
        .focus-title {
            font-family: 'Poppins', sans-serif;
            font-size: 3rem;
            font-weight: 350;
            letter-spacing: 0.15rem;
            text-transform: capitalize;
        }

        .focus-subtitle,
        .focus-description {
            font-family: 'Poppins', sans-serif;
        }

        .focus-subtitle {
            margin-bottom: 1rem;
        }

        .focus-description {
            margin-bottom: 3rem;
        }

        .focus-colaboration {
            font-family: 'Playwrite IS', sans-serif;
        }

        .login-subtitle {
            font-size: 1.8rem;
            font-weight: 480;
            letter-spacing: 0.2rem;
            text-transform: capitalize;
        }

        .login-description {
            font-size: 0.8rem;
            font-weight: 300;
            letter-spacing: 0.1rem;
        }

        .login-card {
            background-color: #ffffff;
            padding: 2rem 0.5rem;
            display: flex;
            width: 80%;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .login-button {
            background-color: #232d69;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .login-button:hover {
            background-color: #0056b3;
        }

        .login-icon {
            margin-right: 0.5rem;
        }

        .bg-tripatra {
            background: linear-gradient(180deg, #232d69, #3a4a9d) !important;
            color: white !important;
        }

        body::after {
            content: '';
            position: fixed;
            right: -55%;
            bottom: -52%;
            width: 100%;
            height: 155vh;
            background-image: url('/assets/img/logo.png');
            background-repeat: no-repeat;
            background-size: contain;
            opacity: 0.2;
            z-index: 0;
            pointer-events: none;
        }

        .description-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* align-items: flex-start; */
        }

        /* Responsive Styling */
        @media (max-width: 800px) {
            body::after {
                right: -70%;
                bottom: -60%;
                width: 150%;
                height: 120vh;
                background-size: cover;
                opacity: 0.15;
            }

            .description-section .focus-subtitle,
            .description-section .focus-description,
            .description-section .focus-colaboration,
            .description-section .text-endx {
                display: none;
            }

            .description-section img {
                margin-bottom: 1rem;
            }

            .focus-title {
                font-size: 1.8rem;
                margin-bottom: 0.5rem;
            }

            .login-card {
                width: 100%;
                margin-bottom: 50px;
            }

            .login-button {
                padding: 10px 20px;
                font-size: 0.9rem;
                border-radius: 20px;
            }

            .login-section {
                display: flex;
                justify-content: center;
                align-items: start !important;
                height: 100vh !important;
            }

            hr {
                margin: 0;
            }
        }

        .login-section {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
        }

        .container {
            position: relative;
            z-index: 1;
        }

        body {
            height: 100vh;
        }
    </style>
    <title>{{ $title }}</title>
</head>

<body class="bg-tripatra">
    <section class="login-section py-3">
        <div class="container">
            <div class="row">
                <!-- Description Section (Hidden on Mobile) -->
                <div class="col-md-6 description-section">
                    <div class="text-white">
                        <!-- Logo and Title (Always Visible) -->
                        <div class="d-flex align-items-center">
                            <img class="img-fluid rounded px-2" src="{{ asset('assets/img/logo.png') }}" width="100"
                                alt="Logo">
                            <h1 class="focus-title">Focus App</h1>
                        </div>

                        <!-- Text Content (Hidden on Mobile) -->
                        <hr class="border-primary-subtle mb-4">
                        <h2 class="focus-subtitle">Facility Operations & Control Unified System Application</h2>
                        <p class="focus-description">Simplifying Facility Management, Streamlining Workflows, All in One
                            System.</p>
                        <p class="focus-colaboration">- Facility Management Division -</p>
                        <!-- <div class="text-endx">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor"
                                class="bi bi-grip-horizontal" viewBox="0 0 16 16">
                                <path
                                    d="M2 8a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm3 3a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm0-3a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                            </svg>
                        </div> -->
                    </div>
                </div>

                <!-- Login Form Section -->
                <div class="col-md-6">
                    <div class="card border-0 rounded-4 login-card">
                        <div class="card-body">
                            <h3 class="login-subtitle text-center mb-4">Sign in</h3>
                            <div class="text-center">
                                <img src="{{ asset('assets/img/illustrator.png') }}" class="img-fluid mb-5"
                                    width="220" alt="">
                            </div>
                            <form method="GET" action="{{ route('socialite.redirect') }}">
                                <div>
                                    <button class="btn btn-primary btn-lg login-button" type="submit">
                                        <img src="{{ asset('assets/img/Microsoft_logo.svg') }}" class="login-icon"
                                            width="18" alt="">
                                        <span class="pl-3 login-description">Sign In with Tripatra Account</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>

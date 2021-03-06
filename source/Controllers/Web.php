<?php

namespace Source\Controllers;

use Source\Models\User;

/**
 * Class Web
 * @package Source\Controllers
 */
class Web extends Controller
{
    /**
     * Web constructor.
     * @param $router
     */
    public function __construct($router)
    {
        parent::__construct($router);

        if (!empty($_SESSION["user"])) {
            $this->router->redirect("app.home");
        }
    }

    /**
     *
     */
    public function login(): void
    {
        $head = $this->seo->optimize(
            "Faça login para continuar | ".site("name"),
            site("desc"),
            $this->router->route("web.login"),
            routeImage("Login")
        )->render();

        $csrfToken = csrfToken();

        echo $this->view->render("theme/login", [
            "head" => $head,
            "csrf" => $csrfToken
        ]);
    }

    /**
     *
     */
    public function register(): void
    {
        $head = $this->seo->optimize(
            "Crie sua conta | ".site("name"),
            site("desc"),
            $this->router->route("web.register"),
            routeImage("Register")
        )->render();

        $userData = new \StdClass();
        $userData->first_name = null;
        $userData->last_name = null;
        $userData->email = null;
        $userData->connectedTo = null;

        $socialUser = null;

        $csrfToken = csrfToken();

        /**
         * Check if there's a session with "_auth" at the end of the string
         */
        if ($connectedSocialMedia = preg_array_key_exists(
            "/_auth$/",
            $_SESSION)
        ) {
            $socialUser = unserialize($_SESSION[$connectedSocialMedia[0]]);
            $userData->connectedTo = ucfirst(
                explode("_", $connectedSocialMedia[0])[0]
            );
        }

        if ($socialUser) {
            $userData->first_name = $socialUser->getFirstName();
            $userData->last_name = $socialUser->getLastName();;
            $userData->email = $socialUser->getEmail();;
        }

        echo $this->view->render("theme/register", [
            "head" => $head,
            "user" => $userData,
            "csrf" => $csrfToken
        ]);
    }

    /**
     *
     */
    public function forget(): void
    {
        $head = $this->seo->optimize(
            "Recupere sua semha | ".site("name"),
            site("desc"),
            $this->router->route("web.forget"),
            routeImage("Forget")
        )->render();

        $csrfToken = csrfToken();

        echo $this->view->render("theme/forget", [
            "head" => $head,
            "csrf" => $csrfToken
        ]);
    }

    /**
     * @param $data
     */
    public function reset($data): void
    {
        if (empty($_SESSION["forget"])) {
            flash("info", "Informe seu email para recuperar a senha");
            $this->router->redirect("web.forget");
        }

        $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
        $forget = filter_var($data["forget"], FILTER_DEFAULT);

        $errForget = "Não foi possível recuperar, tente novamente";

        if (!$email || !$forget) {
            flash("error", $errForget);
            $this->router->redirect("web.forget");
        }

        $user = (new User())
            ->find(
                "email = :email AND forget = :forget",
                "email={$email}&forget={$forget}")
            ->fetch();

        if (!$user) {
            flash("error", $errForget);
            $this->router->redirect("web.forget");
        }

        $head = $this->seo->optimize(
            "Crie sua nova senha | ".site("name"),
            site("desc"),
            $this->router->route("web.reset"),
            routeImage("Reset")
        )->render();

        echo $this->view->render("theme/reset", [
            "head" => $head
        ]);
    }

    /**
     * @param $data
     */
    public function error($data): void
    {
        $error = filter_var($data["errcode"], FILTER_VALIDATE_INT);

        $head = $this->seo->optimize(
            "Ooops {$error} | ".site("name"),
            site("desc"),
            $this->router->route("web.error", ["errcode" => $error]),
            routeImage($error)
        )->render();

        echo $this->view->render("theme/error", [
            "head" => $head,
            "error" => $error
        ]);
    }
}
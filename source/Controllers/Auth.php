<?php

namespace Source\Controllers;

use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use Source\Models\User;
use Source\Support\Email;

/**
 * Class Auth
 * @package Source\Controllers
 */
class Auth extends Controller
{
    /**
     * Auth constructor.
     * @param $router
     */
    public function __construct($router)
    {
        parent::__construct($router);
    }

    /**
     * @param $data
     */
    public function login($data): void
    {
        $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
        $password = filter_var($data["passwd"], FILTER_DEFAULT);
        $csrf = filter_var($data["csrf"], FILTER_DEFAULT);

        if (!csrfToken(true, $csrf)) {
            flash("error", "Origem não autorizada. Está é a página de login REAL");
            $this->router->redirect("web.login");
            return;
        }

        if (!$email || !$password) {
            echo $this->ajaxResponse("message", [
                "type" => "alert",
                "message" => "Informe um email e senha válidos para logar."
            ]);
            return;
        }

        $user = (new User())->find("email = :email", "email={$email}")->fetch();
        
        if (!$user || !password_verify($password, $user->password)) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "Dados inválidos. Por favor informe os dados corretos para logar."
            ]);
            return;
        }

        /** SOCIAL VALIDATION */
        $this->socialValidateAndUserUpdate($user);

        $_SESSION["user"] = $user->id;

        echo $this->ajaxResponse("redirect", ["url" => $this->router->route("app.home")]);
    }

    /**
     * @param $data
     */
    public function register($data): void
    {
        $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

        if (!csrfToken(true, $data["csrf"])) {
            flash("error", "Origem não autorizada. Está é a página de cadastro REAL");
            $this->router->redirect("web.register");
            return;
        }

        if (in_array("", $data)) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "Preencha todos os campos para continuar"
            ]);
            return;
        }

        $user = new User();
        $user->first_name = $data["first_name"];
        $user->last_name = $data["last_name"];
        $user->email = $data["email"];
        $user->password = $data["passwd"];

        /** SOCIAL VALIDATION */
        $this->socialValidateAndUserUpdate($user);

        if (!$user->save()) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => $user->fail()->getMessage()
            ]);
            return;
        }

        $_SESSION["user"] = $user->id;

        echo $this->ajaxResponse("redirect", [
            "url" => $this->router->route("app.home")
        ]);
    }

    /**
     *
     */
    public function disconnect(): void
    {
        if ($connectedSocialMedia = preg_array_key_exists(
            "/_auth$/",
            $_SESSION)
        ) {
            unset($_SESSION[$connectedSocialMedia[0]]);
        }

        $link = $this->router->route("web.login");
        flash(
            "info",
            "Desconectado com sucesso. Caso queira conectar sua conta novamente, acesse a <a href='{$link}'>PÁGINA DE LOGIN</a>"
        );
        $this->router->redirect("web.register");
    }

    /**
     * @param $data
     */
    public function forget($data): void
    {
        $email = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
        $csrf = filter_var($data["csrf"], FILTER_DEFAULT);

        if (!csrfToken(true, $csrf)) {
            flash("error", "Origem não autorizada. Está é a página de reset REAL");
            $this->router->redirect("web.forget");
            return;
        }

        if (!$email) {
            echo $this->ajaxResponse("message", [
                "type" => "alert",
                "message" => "Informe um email válido para recuperar a senha"
            ]);
            return;
        }

        $user = (new User())->find("email = :email", "email={$email}")->fetch();

        if (!$user) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "O e-mail informado não está cadastrado"
            ]);
            return;
        }

        $user->forget = md5(uniqid(rand(), true));
        $user->save();

        $_SESSION["forget"] = $user->id;

        $email = new Email();
        $email->add(
            "Recupere sua senha | ".site("name"),
            $this->view->render("emails/recover", [
                "user" => $user,
                "link" => $this->router->route("web.reset", [
                    "email" => $user->email,
                    "forget" => $user->forget
                ])
            ]),
            "{$user->first_name} {$user->last_name}",
            $user->email
        )->send();

        flash("success", "Enviamos um link de recuperação para seu email");

        echo $this->ajaxResponse("redirect", [
            "url" => $this->router->route("web.forget")
        ]);
    }

    /**
     * @param $data
     */
    public function reset($data): void
    {
        $data = filter_var_array($data, FILTER_SANITIZE_STRIPPED);

        if (!csrfToken(true, $data["csrf"])) {
            flash("error", "Origem não autorizada. Está é a página de reset REAL");
            $this->router->redirect("web.forget");
            return;
        }

        if (
            empty($_SESSION["forget"])
            || !$user = (new User())->findById($_SESSION["forget"])
        ) {
            flash("error", "Não foi possível recuperar, tente novamente");
            echo $this->ajaxResponse("redirect", [
                "url" => $this->router->route("web.forget")
            ]);
            return;
        }

        if (empty($data["password"]) || empty($data["password_re"])) {
            echo $this->ajaxResponse("message", [
               "type" => "alert",
               "message" => "Informe e repita sua nova senha"
            ]);
            return;
        }

        if ($data["password"] != $data["password_re"]) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => "Você informou duas senhas diferentes"
            ]);
            return;
        }

        $user->password = $data["password"];
        $user->forget = null;

        if (!$user->save()) {
            echo $this->ajaxResponse("message", [
                "type" => "error",
                "message" => $user->fail()->getMessage()
            ]);
            return;
        }

        unset($_SESSION["forget"]);
        flash("success", "Sua senha foi atualizada com sucesso");

        echo $this->ajaxResponse("redirect", [
           "url" => $this->router->route("web.login")
        ]);
    }

    /**
     *
     */
    public function facebook(): void
    {
        $facebook = new Facebook(FACEBOOK_LOGIN);
        $error = filter_input(INPUT_GET, "error", FILTER_SANITIZE_STRIPPED);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_STRIPPED);

        if (!empty($_SESSION["google_auth"])) {
            unset($_SESSION["google_auth"]);
        }

        if (!$error && !$code) {
            $authUrl = $facebook->getAuthorizationUrl(["scope" => "email"]);
            header("Location: {$authUrl}");
            return;
        }

        if ($error) {
            flash("error", "Não foi possível logar com o Facebook");
            $this->router->redirect("web.login");
        }

        if ($code && empty($_SESSION["facebook_auth"])) {
            try {
                $token = $facebook->getAccessToken("authorization_code", ["code" => $code]);
                $_SESSION["facebook_auth"] = serialize($facebook->getResourceOwner($token));
            } catch (\Exception $exception) {
                flash("error", "Não foi possível logar com o Facebook");
                $this->router->redirect("web.login");
            }
        }

        /** @var $facebookUser FacebookUser */
        $facebookUser = unserialize($_SESSION["facebook_auth"]);

        // Login by ID
        $userById = (new User())->find("facebook_id = :id", "id={$facebookUser->getId()}")->fetch();
        if ($userById) {
            unset($_SESSION["facebook_auth"]);

            $_SESSION["user"] = $userById->id;
            $this->router->redirect("app.home");
        }

        // Login by email
        $userByEmail = (new User())->find("email = :email", "email={$facebookUser->getEmail()}")->fetch();
        if ($userByEmail) {
            flash("info", "Olá {$facebookUser->getFirstName()}, faça login para conectar seu Facebook");
            $this->router->redirect("web.login");
        }

        $link = $this->router->route("web.login");
        flash(
            "info",
            "Olá {$facebookUser->getFirstName()}, <b>Se já tem uma conta, clique em <a title='Fazer login' href='{$link}'>FAZER LOGIN</a></b>, ou complete seu cadastro"
        );
        $this->router->redirect("web.register");
    }

    /**
     *
     */
    public function google(): void
    {
        $google = new Google(GOOGLE_LOGIN);
        $error = filter_input(INPUT_GET, "error", FILTER_SANITIZE_STRIPPED);
        $code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_STRIPPED);

        if (!empty($_SESSION["facebook_auth"])) {
            unset($_SESSION["facebook_auth"]);
        }

        if (!$error && !$code) {
            $authUrl = $google->getAuthorizationUrl();
            header("Location: {$authUrl}");
            return;
        }

        if ($error) {
            flash("error", "Não foi possível logar com o Google");
            $this->router->redirect("web.login");
        }

        if ($code && empty($_SESSION["google_auth"])) {
            try {
                $token = $google->getAccessToken("authorization_code", ["code" => $code]);
                $_SESSION["google_auth"] = serialize($google->getResourceOwner($token));
            } catch (\Exception $exception) {
                flash("error", "Não foi possível logar com o Google");
                $this->router->redirect("web.login");
            }
        }

        /** @var $googleUser GoogleUser */
        $googleUser = unserialize($_SESSION["google_auth"]);

        // Login by ID
        $userById = (new User())->find("google_id = :id", "id={$googleUser->getId()}")->fetch();
        if ($userById) {
            unset($_SESSION["google_auth"]);

            $_SESSION["user"] = $userById->id;
            $this->router->redirect("app.home");
        }

        // Login by email
        $userByEmail = (new User())->find("email = :email", "email={$googleUser->getEmail()}")->fetch();
        if ($userByEmail) {
            flash("info", "Olá {$googleUser->getFirstName()}, faça login para conectar sua conta do Google");
            $this->router->redirect("web.login");
        }

        $link = $this->router->route("web.login");
        flash(
            "info",
            "Olá {$googleUser->getFirstName()}, <b>Se já tem uma conta, clique em <a title='Fazer login' href='{$link}'>FAZER LOGIN</a></b>, ou complete seu cadastro"
        );
        $this->router->redirect("web.register");
    }

    /**
     * @param User $user
     */
    public function socialValidateAndUserUpdate(User $user): void
    {
        /**
         * FACEBOOK
         */
        if (!empty($_SESSION["facebook_auth"])) {
            /** @var $facebookUser FacebookUser */
            $facebookUser = unserialize($_SESSION["facebook_auth"]);

            $user->facebook_id = $facebookUser->getId();
            $user->photo = $facebookUser->getPictureUrl();
            $user->save();

            unset($_SESSION["facebook_auth"]);
        }

        /**
         * GOOGLE
         */
        if (!empty($_SESSION["google_auth"])) {
            /** @var $googleUser GoogleUser */
            $googleUser = unserialize($_SESSION["google_auth"]);

            $user->google_id = $googleUser->getId();
            $user->photo = $googleUser->getAvatar();
            $user->save();

            unset($_SESSION["google_auth"]);
        }
    }
}
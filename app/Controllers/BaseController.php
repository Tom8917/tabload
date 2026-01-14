<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController fournit un point central pour charger les composants
 * nécessaires à tous vos contrôleurs et réaliser des actions globales.
 * Étendez cette classe dans vos nouveaux contrôleurs :
 *     class Home extends BaseController
 *
 * Pour des raisons de sécurité, déclarez toutes les nouvelles méthodes comme protected ou private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance principale de l'objet Request.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Instance du routeur pour identifier les routes actives.
     *
     * @var \CodeIgniter\Router\RouteCollectionInterface
     */
    protected $router;

    /**
     * Liste des helpers à charger automatiquement à l'instanciation
     * de la classe. Ils seront disponibles dans tous les contrôleurs
     * qui étendent BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Instance de session utilisée dans l'application.
     *
     * @var \CodeIgniter\Session\Session|null
     */
    protected $session;

    /**
     * Indique si la session doit démarrer automatiquement.
     *
     * @var bool
     */
    protected $start_session = true;

    /**
     * Indique si l'authentification est nécessaire.
     *
     * @var bool
     */
    protected $require_auth = true;

    /**
     * Liste des méthodes qui ne nécessitent pas d'authentification.
     *
     * @var array
     */
    protected $noAuthMethods = [];

    /**
     * Liste des permissions nécessaires pour accéder à la page.
     *
     * @var array
     */
    protected $requiredPermissions = ['collaborateur', 'utilisateur', 'administrateur'];

    /**
     * Titre de la page.
     *
     * @var string
     */
    protected $title = 'Home';

    /**
     * Préfixe ajouté automatiquement au titre de la page.
     *
     * @var string
     */
    protected $title_prefix = 'tabload';

    /**
     * Menus à gérer dynamiquement dans l'application.
     *
     * @var array|null
     */
    protected array $menusCache = [];

    /**
     * Chemin de navigation pour la gestion des breadcrumbs.
     *
     * @var array
     */
    protected $breadcrumb = [];

    /**
     * Identifiant du menu courant.
     *
     * @var string
     */
    protected $menu = 'accueil';

    /**
     * Identifiant du menu principal.
     *
     * @var string|null
     */
    protected $mainmenu;

    /**
     * Liste des messages à afficher à l'utilisateur.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Récupère les menus appropriés selon l'utilisateur.
     *
     * @param bool $admin Indique si l'utilisateur est un administrateur.
     * @return array Liste des menus.
     */

    protected function menus(bool $admin = false): array
    {
        $key = $admin ? 'admin' : 'front';

        if (!isset($this->menusCache[$key])) {
            $file = $admin ? 'admin.json' : 'front.json';
            $path = APPPATH . 'Menus/' . $file;

            $this->menusCache[$key] = file_exists($path)
                ? (json_decode(file_get_contents($path), true) ?? [])
                : [];
        }

        return $this->menusCache[$key];
    }

    /**
     * Initialise le contrôleur avec les services de base.
     *
     * @param RequestInterface $request  Instance de la requête HTTP.
     * @param ResponseInterface $response Instance de la réponse HTTP.
     * @param LoggerInterface $logger    Logger pour l'application.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        if ($this->start_session) {
            log_message('debug', 'start session');
            $this->session = session();
            if (session()->has('messages')) {
                $this->messages = session()->getFlashdata('messages');
            }
        }
        $this->router = service('router');

        $currentMethod = strtolower($this->router->methodName());
        if ($this->require_auth && !in_array($currentMethod, $this->noAuthMethods)) {
            $this->checkLogin();
            $this->checkPermission();
        }
    }

    /**
     * Vérifie si l'utilisateur est authentifié.
     *
     * @param bool $redirect Indique si une redirection doit être effectuée en cas d'échec.
     * @return bool True si l'utilisateur est authentifié, False sinon.
     */
    protected function checkLogin($redirect = true)
    {
        if (!$this->session || !$this->session->has('user')) {
            if ($redirect) {
                $this->session->set('redirect_url', current_url(true)->getPath());
                $this->redirect('/login');
            }
            return false;
        }
        return true;
    }

    /**
     * Vérifie les permissions de l'utilisateur.
     *
     * @return bool True si l'utilisateur a les permissions, False sinon.
     */
    public function checkPermission(): bool
    {
        if (!$this->session || !$this->session->has('user')) {
            return false;
        }

        $user = $this->session->get('user');

        if (!$user || !in_array($user->getPermissionSlug(), $this->requiredPermissions, true)) {
            $this->session->set('redirect_url', current_url(true)->getPath());
            $this->redirect('/');
        }

        return true;
    }

    /**
     * Déconnecte l'utilisateur et détruit la session associée.
     *
     * @return void
     */
    public function logout()
    {
        if (isset($this->session->user)) {
            $this->session->remove('user');
        }
        $this->redirect('/login');
    }

    /**
     * Redirige vers une URL donnée.
     *
     * @param string $url URL de destination.
     * @param array $data Données à transmettre via flashdata.
     * @return void
     */
    public function redirect(string $url, array $data = [])
    {
        $url = base_url($url);
        if (!empty($this->messages)) {
            session()->setFlashdata('messages', $this->messages);
        }
        if (!empty($data)) {
            session()->setFlashdata('data', $data);
        }
        header("Location: {$url}");
        exit;
    }

    /**
     * Charge et affiche une vue.
     *
     * @param string|null $vue Nom de la vue à charger.
     * @param array $datas Données à transmettre à la vue.
     * @param bool $admin Indique si le template admin doit être utilisé.
     * @return string Contenu rendu de la vue.
     */
    public function view($vue = null, array $datas = [], $admin = false, array $options = [])
    {
        // Détecte si le controller courant est dans App\Controllers\Admin\
        $isAdminContext = false;
        if ($this->router) {
            $ctrl = (string)$this->router->controllerName(); // ex: App\Controllers\Admin\Dashboard
            $isAdminContext = str_starts_with($ctrl, 'App\\Controllers\\Admin\\');
        }

        // Retro-compat : si 3e param = array => options
        if (is_array($admin)) {
            $options = $admin;
            $admin = $isAdminContext; // IMPORTANT : on ne force plus à false
        } else {
            $admin = (bool)$admin;
            // si tu veux être “safe”, tu peux aussi forcer admin quand contexte admin :
            if ($isAdminContext) {
                $admin = true;
            }
        }

        $connected = ($this->session && $this->session->has('user'));
        $user = $connected ? $this->session->get('user') : null;

        $template_dir = $admin ? "templates/admin/" : "templates/front/";
        $menus = $this->menus((bool)$admin);

        return view($template_dir . 'head', [
                'template_dir' => $template_dir,
                'show_menu' => $connected,
                'mainmenu' => $this->mainmenu,
                'breadcrumb' => $this->breadcrumb,
                'localmenu' => $this->menu,
                'user' => $user,
                'menus' => $menus,
                'title' => sprintf('%s : %s', $this->title, $this->title_prefix),
                'options' => $options,
            ])
            . (($vue !== null) ? view($vue, $datas) : '')
            . view($template_dir . 'footer', ['messages' => $this->messages, 'options' => $options]);
    }

        /**
     * Ajoute un message de succès.
     *
     * @param string $txt Message à afficher.
     * @return void
     */
    public function success($txt)
    {
        log_message('debug', $txt);
        $this->messages[] = ['txt' => $txt, 'class' => 'alert-success', 'toast' => 'success'];
    }

    /**
     * Ajoute un message informatif.
     *
     * @param string $txt Message à afficher.
     * @return void
     */
    public function message($txt)
    {
        log_message('debug', $txt);
        $this->messages[] = ['txt' => $txt, 'class' => 'alert-info', 'toast' => 'info'];
    }

    /**
     * Ajoute un message d'avertissement.
     *
     * @param string $txt Message à afficher.
     * @return void
     */
    public function warning($txt)
    {
        log_message('debug', $txt);
        $this->messages[] = ['txt' => $txt, 'class' => 'alert-warning', 'toast' => 'warning'];
    }

    /**
     * Ajoute un message d'erreur.
     *
     * @param string $txt Message à afficher.
     * @return void
     */
    public function error($txt)
    {
        log_message('debug', $txt);
        $this->messages[] = ['txt' => $txt, 'class' => 'alert-danger', 'toast' => 'error'];
    }

    /**
     * Ajoute un élément au fil d'Ariane.
     *
     * @param string $text Texte de l'élément.
     * @param string|array $url URL ou segments de l'élément.
     * @param string $info Informations supplémentaires.
     * @return void
     */
    protected function addBreadcrumb($text, $url, $info = '')
    {
        if ($this->breadcrumb === null) {
            $this->breadcrumb = [];
        }
        $this->breadcrumb[] = [
            'text' => $text,
            'url' => (is_array($url) ? '/' . implode('/', $url) : $url),
            'info' => $info,
        ];
    }
}
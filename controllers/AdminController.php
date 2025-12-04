<?php

namespace Controllers;

use Models\ArticleManager;
use Models\CommentManager;
use Models\Article;
use Models\Comment;
use Services\Utils;
use Views\View;
use Models\UserManager;

class AdminController
{
    /**
     * Affiche la page d'administration.
     * @return void
     */
    public function showAdmin(): void
    {
        $this->checkIfUserIsConnected();

        $articleManager = new ArticleManager();
        $articles = $articleManager->getAllArticlesWithStats();

        // Gestion du tri
        $sortColumn = Utils::request('sort', 'date');
        $sortOrder = Utils::request('order', 'desc');

        // Validation des paramètres de tri
        $validColumns = ['title', 'views', 'comments', 'date'];
        if (!in_array($sortColumn, $validColumns)) {
            $sortColumn = 'date';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Tri des articles
        usort($articles, function ($a, $b) use ($sortColumn, $sortOrder) {
            $valueA = $this->getSortValue($a, $sortColumn);
            $valueB = $this->getSortValue($b, $sortColumn);

            if ($valueA == $valueB) {
                return 0;
            }

            $result = ($valueA < $valueB) ? -1 : 1;
            return ($sortOrder === 'desc') ? -$result : $result;
        });

        $commentManager = new CommentManager();
        $allComments = [];
        foreach ($articles as $articleData) {
            $article = $articleData['article'];
            $comments = $commentManager->getAllCommentsByArticleId($article->getId());
            $allComments = array_merge($allComments, array_map(function ($comment) use ($article) {
                return [
                    'comment' => $comment,
                    'article_title' => $article->getTitle()
                ];
            }, $comments));
        }

        $view = new View("Administration");
        $view->render("admin", [
            'articles' => $articles,
            'comments' => $allComments,
            'sortColumn' => $sortColumn,
            'sortOrder' => $sortOrder
        ]);
    }

    /**
     * Vérifie que l'utilisateur est connecté.
     * @return void
     */
    private function checkIfUserIsConnected(): void
    {
        // On vérifie que l'utilisateur est connecté.
        if (!isset($_SESSION['user'])) {
            Utils::redirect("connectionForm");
        }
    }

    /**
     * Affichage du formulaire de connexion.
     * @return void
     */
    public function displayConnectionForm(): void
    {
        $view = new View("Connexion");
        $view->render("connectionForm");
    }

    /**
     * Connexion de l'utilisateur.
     * @return void
     */
    public function connectUser(): void
    {
        // On récupère les données du formulaire.
        $login = Utils::request("login");
        $password = Utils::request("password");

        // On vérifie que les données sont valides.
        if (empty($login) || empty($password)) {
            throw new Exception("Tous les champs sont obligatoires. 1");
        }

        // On vérifie que l'utilisateur existe.
        $userManager = new UserManager();
        $user = $userManager->getUserByLogin($login);
        if (!$user) {
            throw new Exception("L'utilisateur demandé n'existe pas.");
        }

        // On vérifie que le mot de passe est correct.
        if (!password_verify($password, $user->getPassword())) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            throw new Exception("Le mot de passe est incorrect : $hash");
        }

        // On connecte l'utilisateur.
        $_SESSION['user'] = $user;
        $_SESSION['idUser'] = $user->getId();

        // On redirige vers la page d'administration.
        Utils::redirect("admin");
    }

    /**
     * Déconnexion de l'utilisateur.
     * @return void
     */
    public function disconnectUser(): void
    {
        // On déconnecte l'utilisateur.
        unset($_SESSION['user']);

        // On redirige vers la page d'accueil.
        Utils::redirect("home");
    }

    /**
     * Affichage du formulaire d'ajout d'un article.
     * @return void
     */
    public function showUpdateArticleForm(): void
    {
        $this->checkIfUserIsConnected();

        // On récupère l'id de l'article s'il existe.
        $id = Utils::request("id", -1);

        // On récupère l'article associé.
        $articleManager = new ArticleManager();
        $article = $articleManager->getArticleById($id);

        // Si l'article n'existe pas, on en crée un vide.
        if (!$article) {
            $article = new Article();
        }

        // On affiche la page de modification de l'article.
        $view = new View("Edition d'un article");
        $view->render("updateArticleForm", [
            'article' => $article
        ]);
    }

    /**
     * Ajout et modification d'un article.
     * On sait si un article est ajouté car l'id vaut -1.
     * @return void
     */
    public function updateArticle(): void
    {
        $this->checkIfUserIsConnected();

        // On récupère les données du formulaire.
        $id = Utils::request("id", -1);
        $title = Utils::request("title");
        $content = Utils::request("content");

        // On vérifie que les données sont valides.
        if (empty($title) || empty($content)) {
            throw new Exception("Tous les champs sont obligatoires. 2");
        }

        // On crée l'objet Article.
        $article = new Article([
            'id' => $id, // Si l'id vaut -1, l'article sera ajouté. Sinon, il sera modifié.
            'title' => $title,
            'content' => $content,
            'id_user' => $_SESSION['idUser']
        ]);

        // On ajoute l'article.
        $articleManager = new ArticleManager();
        $articleManager->addOrUpdateArticle($article);

        // On redirige vers la page d'administration.
        Utils::redirect("admin");
    }


    /**
     * Suppression d'un article.
     * @return void
     */
    public function deleteArticle(): void
    {
        $this->checkIfUserIsConnected();

        $id = Utils::request("id", -1);

        // On supprime l'article.
        $articleManager = new ArticleManager();
        $articleManager->deleteArticle($id);

        // On redirige vers la page d'administration.
        Utils::redirect("admin");
    }

    /**
     * Suppression d'un commentaire.
     * @return void
     */
    public function deleteComment(): void
    {
        $this->checkIfUserIsConnected();

        $id = Utils::request("id", -1);

        $commentManager = new CommentManager();
        $comment = $commentManager->getCommentById($id);

        if (!$comment) {
            throw new Exception("Le commentaire demandé n'existe pas.");
        }

        $commentManager->deleteComment($comment);

        Utils::redirect("admin");
    }

    /**
     * Extrait la valeur de tri pour une colonne donnée.
     * @param array $articleData : les données de l'article
     * @param string $column : la colonne de tri
     * @return mixed : la valeur à utiliser pour le tri
     */
    private function getSortValue(array $articleData, string $column): mixed
    {
        switch ($column) {
            case 'title':
                return strtolower($articleData['article']->getTitle());
            case 'views':
                return (int)$articleData['article']->getViews();
            case 'comments':
                return (int)$articleData['comment_count'];
            case 'date':
                return $articleData['date_creation'];
            default:
                return $articleData['date_creation'];
        }
    }
}

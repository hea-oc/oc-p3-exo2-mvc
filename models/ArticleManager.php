<?php

namespace Models;

/**
 * Classe qui gère les articles.
 */
class ArticleManager extends AbstractEntityManager
{
    /**
     * Récupère tous les articles.
     * @return array : un tableau d'objets Article.
     */
    public function getAllArticles(): array
    {
        $sql = "SELECT * FROM article";
        $result = $this->db->query($sql);
        $articles = [];

        while ($article = $result->fetch()) {
            $articles[] = new Article($article);
        }
        return $articles;
    }

    /**
     * Récupère tous les articles avec statistiques (vues, commentaires, date).
     * @return array : un tableau d'articles avec leurs statistiques.
     */
    public function getAllArticlesWithStats(): array
    {
        // First check if views column exists
        $viewsColumn = $this->checkViewsColumnExists() ? 'a.views' : '0 as views';
        $sql = "SELECT a.*,
            COUNT(c.id) as comment_count,
            DATE_FORMAT(a.date_creation, '%Y-%m-%d %H:%i:%s') as date_creation_formatted, 
            {$viewsColumn} 
            FROM article a 
            LEFT JOIN comment c 
            ON a.id = c.id_article 
            GROUP BY a.id 
            ORDER BY a.date_creation DESC";
        $result = $this->db->query($sql);
        $articles = [];

        while ($article = $result->fetch()) {
            $articleObj = new Article($article);
            $articles[] = [
                'article' => $articleObj,
                'comment_count' => (int)$article['comment_count'],
                'date_creation' => $article['date_creation_formatted']
            ];
        }
        return $articles;
    }

    /**
     * Vérifie si la colonne views existe dans la table article.
     * @return bool
     */
    private function checkViewsColumnExists(): bool
    {
        try {
            $sql = "SHOW COLUMNS FROM article LIKE 'views'";
            $result = $this->db->query($sql);
            return $result->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupère un article par son id.
     * @param int $id : l'id de l'article.
     * @return Article|null : un objet Article ou null si l'article n'existe pas.
     */
    public function getArticleById(int $id): ?Article
    {
        $sql = "SELECT * FROM article WHERE id = :id";
        $result = $this->db->query($sql, ['id' => $id]);
        $article = $result->fetch();
        if ($article) {
            return new Article($article);
        }
        return null;
    }

    /**
     * Ajoute ou modifie un article.
     * On sait si l'article est un nouvel article car son id sera -1.
     * @param Article $article : l'article à ajouter ou modifier.
     * @return void
     */
    public function addOrUpdateArticle(Article $article): void
    {
        if ($article->getId() == -1) {
            $this->addArticle($article);
        } else {
            $this->updateArticle($article);
        }
    }

    /**
     * Ajoute un article.
     * @param Article $article : l'article à ajouter.
     * @return void
     */
    public function addArticle(Article $article): void
    {
        $sql = "INSERT INTO article (id_user, title, content, date_creation) 
            VALUES (:id_user, :title, :content, NOW())";
        $this->db->query($sql, [
            'id_user' => $article->getIdUser(),
            'title' => $article->getTitle(),
            'content' => $article->getContent()
        ]);
    }

    /**
     * Modifie un article.
     * @param Article $article : l'article à modifier.
     * @return void
     */
    public function updateArticle(Article $article): void
    {
        $sql = "UPDATE article 
            SET title = :title, content = :content, date_update = NOW() 
            WHERE id = :id";
        $this->db->query($sql, [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
            'id' => $article->getId()
        ]);
    }

    /**
     * Supprime un article.
     * @param int $id : l'id de l'article à supprimer.
     * @return void
     */
    public function deleteArticle(int $id): void
    {
        $sql = "DELETE FROM article WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Incrémente le nombre de vues.
     * @param int $id : l'id de l'article.
     * @return void
     */
    public function incrementViews(int $id): void
    {
        if (!$this->checkViewsColumnExists()) {
            return;
        }
        $sql = "UPDATE article SET views = views + 1 WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
    }
}

<?php

    /**
     * Ce template affiche la page admin.
     */

    use Services\Utils;

    $titleSortOrder = ($sortColumn === 'title' && $sortOrder === 'asc') ? 'desc' : 'asc';
    $viewsSortOrder = ($sortColumn === 'views' && $sortOrder === 'asc') ? 'desc' : 'asc';
    $commentsSortOrder = ($sortColumn === 'comments' && $sortOrder === 'asc') ? 'desc' : 'asc';
    $dateSortOrder = ($sortColumn === 'date' && $sortOrder === 'asc') ? 'desc' : 'asc';
?>

<h2>Tableau de bord de monitoring</h2>

<div class="adminStats">
    <table id="articlesTable">
        <thead>
        <tr>
                <th class="sortable <?= $sortColumn === 'title' ? 'sorted sorted-' . $sortOrder : '' ?>">
                    <a href="index.php?action=admin&sort=title&order=<?= $titleSortOrder ?>">
                        Titre
                        <?php if ($sortColumn === 'title') : ?>
                            <span class="sort-indicator"><?= $sortOrder === 'asc' ? '↑' : '↓' ?></span>
                        <?php else : ?>
                            <span class="sort-indicator sort-neutral">↕</span>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="sortable <?= $sortColumn === 'views' ? 'sorted sorted-' . $sortOrder : '' ?>">
                    <a href="index.php?action=admin&sort=views&order=<?= $viewsSortOrder ?>">
                        Vues
                        <?php if ($sortColumn === 'views') : ?>
                            <span class="sort-indicator"><?= $sortOrder === 'asc' ? '↑' : '↓' ?></span>
                        <?php else : ?>
                            <span class="sort-indicator sort-neutral">↕</span>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="sortable <?= $sortColumn === 'comments' ? 'sorted sorted-' . $sortOrder : '' ?>">
                    <a href="index.php?action=admin&sort=comments&order=<?= $commentsSortOrder ?>">
                        Commentaires
                        <?php if ($sortColumn === 'comments') : ?>
                            <span class="sort-indicator"><?= $sortOrder === 'asc' ? '↑' : '↓' ?></span>
                        <?php else : ?>
                            <span class="sort-indicator sort-neutral">↕</span>
                        <?php endif; ?>
                    </a>
                </th>
                <th class="sortable <?= $sortColumn === 'date' ? 'sorted sorted-' . $sortOrder : '' ?>">
                    <a href="index.php?action=admin&sort=date&order=<?= $dateSortOrder ?>">
                        Date de publication
                        <?php if ($sortColumn === 'date') : ?>
                            <span class="sort-indicator"><?= $sortOrder === 'asc' ? '↑' : '↓' ?></span>
                        <?php else : ?>
                            <span class="sort-indicator sort-neutral">↕</span>
                        <?php endif; ?>
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $articleData) {
                $article = $articleData['article'];
                $views = $article->getViews();
                $commentCount = $articleData['comment_count'];
                $dateCreation = DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $articleData['date_creation']
                );
                ?>
            <tr class="articleRow">
                <td class="title" data-title="<?= Utils::format($article->getTitle()) ?>">
                    <a href="index.php?action=showArticle&id=<?= $article->getId() ?>">
                        <?= Utils::format($article->getTitle()) ?>
                    </a>
                </td>
                <td data-views="<?= $views ?>"><?= $views ?></td>
                <td data-comments="<?= $commentCount ?>"><?= $commentCount ?></td>
                <td data-date="<?= $dateCreation ? $dateCreation->getTimestamp() : 0 ?>">
                    <?= $dateCreation ? $dateCreation->format('d/m/Y H:i') : 'N/A' ?>
                </td>
                <td class="actions">
                    <a class="submit" href="index.php?action=showUpdateArticleForm&id=<?= $article->getId() ?>">
                        Modifier
                    </a>
                    <a class="submit" href="index.php?action=deleteArticle&id=<?= $article->getId() ?>" 
                        <?= Utils::askConfirmation("Supprimer cet article ?") ?>>Supprimer</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<h2>Gestion des commentaires</h2>

<div class="adminComments">
    <?php if (empty($comments)) { ?>
        <p>Aucun commentaire à afficher.</p>
    <?php } else { ?>
        <?php foreach ($comments as $commentData) {
            $comment = $commentData['comment'];
            ?>
        <div class="commentItem">
            <div class="commentHeader">
                <span class="commentAuthor">
                    <strong>
                        <?= Utils::format($comment->getPseudo()) ?>
                    </strong>
                </span>
                <span class="commentArticle">
                    sur "<?= Utils::format($commentData['article_title']) ?>"
                </span>
                <span class="commentDate">
                    <?= $comment->getDateCreation()->format('d/m/Y H:i') ?>
                </span>
            </div>
            <div class="commentContent">
                <?= nl2br(Utils::format($comment->getContent())) ?>
            </div>
            <div class="commentActions">
                <a class="submit deleteBtn" href="index.php?action=deleteComment&id=<?= $comment->getId() ?>" 
                <?= Utils::askConfirmation("Êtes-vous sûr de vouloir supprimer ce commentaire ?") ?>>
                Supprimer
                </a>
            </div>
        </div>
        <?php } ?>
    <?php } ?>
</div>

<a class="submit" href="index.php?action=showUpdateArticleForm">Ajouter un article</a>

<?php
<?php /** @var array $book */ ?>
<article class="book-card">

    <a href="detail.php?id=<?= $book["id"] ?>"
       class="book-cover">

        <img src="<?= e($book["image"]) ?>"
             alt="<?= e($book["title"]) ?>">

        <?php if ($book["featured"]): ?>
            <span>Nổi bật</span>
        <?php endif; ?>

    </a>

    <div class="book-info">

        <small><?= e($book["category"]) ?></small>

        <h3>
            <a href="detail.php?id=<?= $book["id"] ?>">
                <?= e($book["title"]) ?>
            </a>
        </h3>

        <p><?= e($book["author"]) ?></p>

        <div class="book-bottom">

            <strong>
                <?= money($book["price"]) ?>
            </strong>
             <?php if (!isAdmin()): ?>
            <a href="cart_action.php?action=add&id=<?= $book["id"] ?>"
               class="add-link">
                + Giỏ hàng
            </a>
              <?php endif; ?>
        </div>

    </div>

</article>

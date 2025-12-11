<div class="container py-4">
    <h1>Commande validée !</h1>

    <div class="alert alert-success">
        Merci pour votre commande. Voici le détail de la préparation :
    </div>

    <ul class="list-group mb-4">
        <li class="list-group-item">
            <strong>Volume total commandé :</strong> <?= esc($volumeTotal) ?> ml
        </li>
        <li class="list-group-item">
            <strong>Taux de nicotine :</strong> <?= esc($nicStrength) ?> mg/ml
        </li>
        <li class="list-group-item">
            <strong>Volume d’arôme (ml) :</strong> <?= esc($volumeArome) ?> ml
        </li>
        <li class="list-group-item">
            <strong>Volume de nicotine (ml) :</strong> <?= esc($volumeNic) ?> ml
        </li>
        <li class="list-group-item">
            <strong>Volume de base (ml) :</strong> <?= esc($volumeBase) ?> ml
        </li>
    </ul>

    <p>
        Votre commande a bien été enregistrée en tant que <strong>#<?= esc($orderId) ?></strong>.<br>
        Vous pouvez la retrouver dans l’espace admin « Commandes » pour suivre son traitement.
    </p>

    <a href="<?= site_url('recipe') ?>" class="btn btn-primary">Retour aux recettes</a>
</div>

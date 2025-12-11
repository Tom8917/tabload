<div class="container py-4">
    <h1>Récapitulatif de la commande</h1>

    <table class="table table-bordered mb-4">
        <thead>
        <tr>
            <th>Recette</th>
            <th>Quantité</th>
            <th>Sous-total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($cartItems as $item): ?>
            <tr>
                <td><?= esc($item['name']) ?></td>
                <td><?= $item['qty'] ?></td>
                <td><?= number_format($item['subtotal'], 2, ',', ' ') ?> €</td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="2" class="text-end"><strong>Total :</strong></td>
            <td><strong><?= number_format($total, 2, ',', ' ') ?> €</strong></td>
        </tr>
        </tbody>
    </table>

    <form action="<?= site_url('cart/pay') ?>" method="post" class="mb-4">
        <?= csrf_field() ?>
        <h4>Informations de paiement</h4>
        <p><em>
                Pour l’instant, le paiement est simulé (gratuit). En production, on décommente l’intégration Stripe
                dans le contrôleur pour envoyer l’utilisateur vers Stripe Checkout.
            </em></p>

        <button type="submit" class="btn btn-primary">Valider la commande</button>
        <a href="<?= site_url('cart') ?>" class="btn btn-secondary">Modifier mon panier</a>
    </form>

    <!-- Si vous voulez afficher la clé publique Stripe et intégrer Stripe.js plus tard : -->
    <!--
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe("<?= esc(env('stripe.public_key')) ?>");
        // Ici, vous pourriez initialiser un formulaire card Element, etc.
    </script>
    -->
</div>

<!-- Charger Stripe.js en mode front -->
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Récupère la clé publique depuis l’environnement
    const stripePublicKey = "<?= esc(env('stripe.public_key')) ?>";
    const stripe = Stripe(stripePublicKey);

    // … ici, vous pourrez créer un form CardElement si vous ne passez pas par Checkout Session …
</script>

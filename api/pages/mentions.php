<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$currentPage = 'mentions';
$pageTitle = 'Mentions Légales & CGV';

include_once __DIR__ . '/../includes/header.php';
?>

<section class="legal-page">
    <aside class="legal-nav">
        <h3>Sommaire</h3>
        <ul>
            <li><a href="#legal">1. Mentions Légales</a></li>
            <li><a href="#hebergement">2. Hébergement & Données</a></li>
            <li><a href="#cgu">3. Conditions d'Utilisation (CGU)</a></li>
            <li><a href="#cgv">4. Conditions de Vente (CGV)</a></li>
            <li><a href="#privacy">5. Données Personnelles (RGPD)</a></li>
        </ul>
    </aside>

    <div class="legal-content">
        <h1>Informations Juridiques</h1>
        <p>Dernière mise à jour : Mai 2026</p>

        <article id="legal">
            <h2>1. Mentions Légales</h2>

            <p><strong>Éditeur du site :</strong><br>
                Ce site est un projet pédagogique réalisé dans le cadre du cursus "Pré-ING2" à CY Tech.<br>
                <strong>Groupe :</strong> MI2-A<br>
                <strong>Propriétaires et Créateurs :</strong><br>
                - Myriam BENSAID<br>
                - Sheryne OUARGHI<br>
                - Kylian VANDEL<br>
                <br>
                <strong>Adresse de contact :</strong><br>
                CY Tech<br>
                Avenue du Parc, 95000 Cergy, France<br>
                Email : legrandmiam@yumland.fr (Email fictif)</p>

        </article>

        <hr>

        <article id="hebergement">
            <h2>2. Hébergement & Serveurs</h2>
            <p>L'architecture technique de ce site repose sur des services cloud modernes garantissant sécurité et haute disponibilité :</p>
            <ul>
                <li><strong>Hébergement de l'application (Front-end & Back-end PHP) :</strong> Le site est déployé et hébergé par la société <strong>Vercel Inc.</strong> (340 S Lemon Ave #4133 Walnut, CA 91789, USA).</li>
                <li><strong>Hébergement de la Base de Données :</strong> Les données applicatives sont stockées de manière sécurisée sur une base de données MySQL hébergée dans le cloud par la société <strong>Aiven</strong> (Aiven Oy, Kampinkuja 2, 00100 Helsinki, Finlande).</li>
            </ul>
        </article>

        <hr>

        <article id="cgu">
            <h2>3. Conditions Générales d'Utilisation (CGU)</h2>
            <h3>3.1 Objet</h3>
            <p>Les présentes CGU régissent l'utilisation du site "Le Grand Miam". L'accès au site implique l'acceptation sans réserve de ces conditions.</p>

            <h3>3.2 Accès au service</h3>
            <p>Le site est accessible gratuitement à tout utilisateur disposant d'un accès internet. L'éditeur met en œuvre tous les moyens pour assurer un accès de qualité, mais ne peut être tenu responsable de tout dysfonctionnement du réseau ou des serveurs.</p>

            <h3>3.3 Propriété Intellectuelle</h3>
            <p>Tous les éléments (textes, images, logos) sont la propriété exclusive de Le Grand Miam SAS ou font l'objet d'une autorisation d'utilisation. Toute reproduction est interdite sans accord préalable.</p>
        </article>

        <hr>

        <article id="cgv">
            <h2>4. Conditions Générales de Vente (CGV)</h2>
            <h3>4.1 Commandes</h3>
            <p>Les commandes peuvent être passées en ligne via le compte client ou sur place. Le client s'engage à fournir des informations exactes lors de sa commande.</p>

            <h3>4.2 Prix et Paiement</h3>
            <p>Les prix sont indiqués en Euros (€) toutes taxes comprises (TTC). Le Grand Miam se réserve le droit de modifier ses prix à tout moment, mais le produit sera facturé sur la base du tarif en vigueur au moment de la validation de la commande.</p>

            <h3>4.3 Rétractation</h3>
            <p>Conformément à l'article L.221-28 du Code de la consommation, le droit de rétractation ne s'applique pas aux contrats portant sur la fourniture de biens susceptibles de se détériorer ou de se périmer rapidement (denrées alimentaires).</p>
        </article>

        <hr>

        <article id="privacy">
            <h2>5. Données Personnelles (RGPD)</h2>
            <p>Les informations recueillies font l’objet d’un traitement informatique destiné à la gestion des commandes. Conformément à la loi « Informatique et Libertés », vous bénéficiez d’un droit d’accès et de rectification aux informations qui vous concernent.</p>
            <p>Vos mots de passe sont hachés et sécurisés via des algorithmes de cryptographie robustes. Aucune donnée n'est revendue à des tiers.</p>
        </article>

    </div>
</section>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>
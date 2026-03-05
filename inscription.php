<?php
session_start();
include('connexionSQL.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['psw'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';  // Nouveau champ "nom"

    if (!empty($email) && !empty($password) && !empty($prenom) && !empty($nom)) { // Vérification du nom aussi
        try {
            // Insérer l'utilisateur en base
            $stmt = $cnn->prepare("INSERT INTO membre (idMemb, mdpMemb, prenomMemb, nomMemb) VALUES (:email, :psw, :prenom, :nom)");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':psw', $password, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);  // Lier le champ "nom"
            $stmt->execute();

            // Démarrer la session et stocker les infos de l'utilisateur
            $_SESSION['nomMemb'] = $nom;  // Enregistrer le nom dans la session
            $_SESSION['prenomMemb'] = $prenom;

            // Rediriger vers index.php en étant connecté
            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Erreur SQL : " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Veuillez remplir tous les champs.</p>";
    }
}
?>

<?php
        // Définir le fuseau horaire
date_default_timezone_set('UTC');

// Fonction pour formater la date
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 1) {
        return 'hier';
    } elseif ($diff->d == 2) {
        return 'avant-hier';
    } elseif ($diff->d > 2 && $diff->d < 7) {
        return 'il y a ' . $diff->d . ' jours';
    } elseif ($diff->d >= 7) {
        return 'le ' . $ago->format('d F');
    } elseif ($diff->h > 0) {
        return 'il y a ' . $diff->h . ' heures';
    } elseif ($diff->i > 0) {
        return 'il y a ' . $diff->i . ' minutes';
    } else {
        return 'à l’instant';
    }
}

 ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="background"></div>

    <div class="content">
        <div class="form-container">
            <h1>Créer un compte</h1>

            <form action="inscription.php" method="POST">
    <div class="form-group">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required placeholder="Entrez votre nom">
    </div>

    <div class="form-group">
        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" required placeholder="Entrez votre prénom">
    </div>

    <div class="form-group">
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required placeholder="Entrez votre email">
    </div>

    <div class="form-group">
        <label for="psw">Mot de passe :</label>
        <input type="password" id="psw" name="psw" required placeholder="Choisissez un mot de passe">
    </div>

    <div class="form-group">
        <label for="confirmer-motdepasse">Confirmer le mot de passe :</label>
        <input type="password" id="confirmer-motdepasse" name="confirmer-motdepasse" required placeholder="Confirmez votre mot de passe">
    </div>

    <div class="form-group">
        <label for="conditions" class="checkbox-label">
            <input type="checkbox" id="conditions" name="conditions" required>
            J'accepte les <a href="javascript:void(0);" id="show-cgu">Conditions Générales d'Utilisation</a>
        </label>
    </div>

    <div class="form-group">
        <button type="submit">S'inscrire</button>
    </div>

    <div class="links">
        <p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
        <p><a href="index.php">Retour à l'accueil</a></p>
    </div>
</form>

        </div>
    </div>

</body>
</html>


    <!-- Modale des CGU -->
    <div id="conditionsModal" class="modal">
        <div class="modal-content">
            <span id="closeModalBtn" class="close">&times;</span>
            <h2>Responsabilité de l'utilisateur</h2>
            <!-- Ajoute le contenu des CGU ici -->
            <h3>Sécurité de son compte</h3>
            <p>A la création d'un forum ou à l'inscription sur un forum, l'Utilisateur est amené à choisir un nom d'utilisateur et un mot de passe. L'Utilisateur est seul responsable de la confidentialité de ses identifiants, et le demeure en cas d'actions non autorisées effectuées par un tiers grâce à ceux-ci. Il est conseillé, à ce titre, de mettre fin à la session (déconnexion) à l'issue de l'utilisation des services. En cas d'utilisation frauduleuse de ses identifiants, l'Utilisateur a l'obligation d'informer sans délai forumactif.com en indiquant les violations ayant pu être commises.</p>

            <h3>Utilisation des services</h3>
            <p>Les contenus publiés par le biais des services, de manière publique ou non, et quelle que soit leur nature (notamment, mais non exclusivement : information, code, donnée, texte, logiciel, musique, son, photographie, image, graphique, vidéo, chat, messages, dossiers) engagent la responsabilité de l'Utilisateur à l'origine de la publication. L'Utilisateur déclare être titulaire de tous les droits et autorisations nécessaires à la diffusion de ces contenus, et s'engage à ne pas publier de contenus contraires aux présentes Conditions. En aucun cas forumactif.com ne peut être tenu pour responsable des conséquences d'une telle publication, ou des pertes ou dommages en résultant.</p>
            <p>Toutes informations, codes, données, textes, logiciels, musiques, sons, photographies, images, graphiques, vidéos, chats, messages, dossiers, ou contenus d'autres natures publiés par le biais des services n'engagent que la responsabilité de l'Utilisateur à l'origine de la publication, que celle-ci ait eu lieu publiquement ou non. En aucun cas forumactif.com ne peut être tenu pour responsable des erreurs, inexactitudes ou omissions au sein d'un contenu publié, ou des pertes et dommages nés ou pouvant naitre de tels contenus.</p>
            
            <h3>Dommages causés</h3>
            <p>L'Utilisateur est responsable des dommages de toute nature, matériels ou immatériels, directs ou indirects, causés à tout tiers, ainsi qu'à forumactif.com du fait de l'utilisation ou de l'exploitation illicite des services, quels que soient la cause et le lieu de survenance de ces dommages et garantit forumactif.com des conséquences des réclamations ou actions dont elle pourrait faire l'objet. L'Utilisateur renonce en outre à exercer tout recours contre forumactif.com dans le cas de poursuites diligentées par un tiers à son encontre du fait de l'utilisation et/ou de l'exploitation illicite des services.</p>

            <h3>Obligations de tout utilisateur</h3>
            <p>L'Utilisateur s'engage à faire des services un usage conforme au but pour lequel ils ont été conçus, et en tout point conforme aux présentes Conditions. A cet égard, il est rappelé que, conformément aux dispositions précédentes, l'Utilisateur est seul responsable des contenus qu'il publie.</p>
            <p>L'Utilisateur s'engage à ne pas participer à toute action ayant pour objet ou pour effet d'attenter au bon fonctionnement des services, notamment mais non exclusivement par (I) tous comportements de nature à interrompre, suspendre, ralentir ou empêcher la continuité des Services, (II) toutes intrusions ou tentatives d'intrusions dans les systèmes de forumactif.com, (III) tous détournements des ressources système du site, (IV) toutes actions de nature à imposer une charge disproportionnée sur les infrastructures de cette dernière, (V) toutes atteintes aux mesures de sécurité et d'authentification, (VI) tous actes de nature à porter atteinte aux droits et intérêts financiers, commerciaux ou moraux de forumactif.com ou des utilisateurs de son site.<p>
            <p>En utilisant les services proposés par forumactif.com, l'Utilisateur accepte que les Administrateurs du forum soient seuls en charge de la gestion de celui-ci, et admet notamment que ceux-ci modère les contenus postés, et gère les Membres.</p>
            <p>L'Utilisateur s'engage à respecter les droits de propriété intellectuelle des tiers, et de forumactif.com. L'ensemble des éléments visibles sur le site est protégé par la législation sur le droit d'auteur. Il ne peut en aucun cas utiliser, distribuer, copier, reproduire, modifier, dénaturer ou transmettre tout ou partie du site ou de ses éléments, tels que textes, images, vidéos, sans l'autorisation écrite et préalable de la société. Les marques et logos figurant sur le site, sont la propriété de la société ou font l'objet d'une autorisation d'utilisation. Aucun droit ou licence ne saurait être attribué sur l'un quelconque de ces éléments sans l'autorisation écrite de la société ou du tiers, détenteur des droits sur la marque ou logo figurant sur le site. La société se réserve le droit de poursuivre tout acte de contrefaçon de ses droits de propriété intellectuelle, y compris dans le cadre d'une action pénale.<p>

            <h2>Comportements et contenus prohibés</h2>
            <p>En utilisant les services proposés par forumactif.com, l'Utilisateur s'engage à en faire un usage conforme au but pour lequel ils ont été conçus, et à ne pas utiliser les produits et services afin – notamment – d'inciter, de favoriser, d'accueillir ou de présenter sous un jour favorable :</p>
            <ul>
                <li>Le piratage, hacking, spamming, et attaques contre des réseaux et/ou serveurs, le phishing, le malware, l'intrusion dans le réseau de tiers,</li>
                <li>Les contenus à caractère sexuel, obscène, pornographique,</li>
                <li>Les contenus violents, diffamants, discriminants, incitants à la haine raciale, les crimes contre l'humanité,</li>
                <li>Le partage, l'hébergement, la diffusion ou le piratage d'œuvre et contenus protégés par le droit d'auteur et la propriété intellectuelle, ou toute pratique contrefaisante,</li>
                <li>La vente, l'échange ou le don de produits soumis à législation spéciale, de médicaments soumis ou non à prescription médicale, de produits stupéfiants et autres substances illicites,</li>
                <li>La fraude à la carte bancaire, ou les pratiques trompeuses.</li>
                <li>Les atteintes aux droits et aux intérêts des mineurs,</li>
                <li>Tout comportement contraire aux lois en vigueur, portant atteinte aux droits des tiers, ou préjudiciable à ceux-ci.</li>
            </ul>
            <p>forumactif.com est un service gratuit mettant tout en œuvre pour offrir un service de qualité à la pointe de la technologie. A l'exception de ce qui est rendu possible par la gestion des crédits, il est formellement interdit de supprimer, de masquer, ou de rendre illisible par quelque moyen que ce soit les mentions obligatoires et copyrights figurant sur les Forums (notamment dans la barre d'outil et le pied de page du forum), ainsi que les contenus sponsorisés ou les publicités. Ces éléments peuvent être retirés par le biais de la gestion des crédits uniquement.</p>
            <p>forumactif.com se réserve le droit de supprimer les forums, messages ou utilisateurs faisant un usage des services manifestement illégal ou contraire aux présentes Conditions, sans préavis. A ce titre, les forums contenant des textes, liens, images, animations, vidéos, petites annonces ou contenus d'une autre nature considérés comme contraires aux présentes Conditions sont susceptibles d'être supprimés sans préavis.</p>

            <h2>Données personnelles et cookies</h2>

            <h3>Utilisation des données personnelles</h3>
            <p>Le traitement de vos données personnelles repose sur votre consentement, hors les cas où les données sont nécessaires à l'exécution d'un contrat, ou au respect d'une obligation légale. Dans les cas où ce traitement repose sur le consentement de l'Utilisateur, il peut retirer son consentement à tout moment afin de le faire cesser selon les modalités prévues dans notre Politique de confidentialité. Le retrait du consentement ne compromet pas la licéité du traitement fondé sur le consentement effectué avant ce retrait.</p>
            <p>Toutes les informations que nous recueillons sont utiles au bon fonctionnement du service, et permettent notamment de personnaliser votre expérience d'utilisateur, d'améliorer l'affichage et le fonctionnement des pages, et, le cas échéant, fournir des résultats publicitaires personnalisés. Certaines de ces données sont susceptibles d'être utilisés si l'Utilisateur y a consenti, notamment s'agissant de la réception de messages électroniques d'information (newsletters) de la part des forums sur lesquels il s'est inscrit.</p>
            <p>L'Utilisateur bénéficie d'un droit d'accès, de rectification et de suppression des données personnelles le concernant, ainsi que du droit d'introduire une réclamation auprès d'une autorité de contrôle. Les modalités de l'exercice de ces droits sont détaillées dans notre Politique de confidentialité.</p>
            <p>L'utilisation des services proposés par forumactif.com est subordonnée à la lecture et à l'acceptation par l'Utilisateur de la Politique de confidentialité.</p>

            <h3>Utilisation des cookies</h3>
            <p>Un cookie est un fichier texte contenant un nombre limité d'informations, qui est téléchargé sur l'appareil de l'utilisateur lorsqu'il visite un site internet. Il permet ainsi au site d'identifier l'utilisateur et de mémoriser certaines informations sur sa navigation, ou de lui offrir des services additionnels, comme la gestion des sessions, ou des publicités.</p>
            <p>forumactif.com se réserve le droit d'utiliser des cookies pour améliorer l'expérience utilisateur, personnaliser la publicité, et analyser les tendances de l'utilisation des services. Ces cookies peuvent être désactivés par l'utilisateur dans les paramètres de son navigateur, mais cela pourrait nuire à certaines fonctionnalités du site.</p>
        </div>
    </div>
            
            
        </div>
    </div>

    <script>
        // Script pour afficher la modale des CGU
        const modal = document.getElementById("conditionsModal");
        const showCgu = document.getElementById("show-cgu");
        const closeBtn = document.getElementsByClassName("close")[0];


        showCgu.onclick = function() {
            modal.style.display = "flex";
        }

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
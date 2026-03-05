<?php 
session_start(); 
include('connexionSQL.php'); 

if (isset($_SESSION['prenomMemb'])) {
    $prenomUtilisateur = $_SESSION['prenomMemb'];
} else {
    $prenomUtilisateur = "Invité";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" />
    <title>Forum article</title>
</head>
<body>
    <header>
        <a href="index.php" class="logo">Forum</a>
        <span>Bienvenue, <?php echo htmlspecialchars($prenomUtilisateur); ?> !</span>

        <?php if ($prenomUtilisateur == "Invité"): ?>
            <button type="submit" onclick="window.location.href = 'connexion.php'">Connexion</button>
        <?php else: ?>
            <button type="submit" onclick="window.location.href = 'deconnexion.php'">Déconnexion</button>
        <?php endif; ?>
    </header>

    <?php date_default_timezone_set('UTC'); ?>

    <?php 
    $resultat = $cnn->prepare("SELECT * FROM article 
        INNER JOIN rubrique ON article.idRub = rubrique.idRub
        INNER JOIN membre ON article.idMemb = membre.idMemb
        INNER JOIN reponse ON article.idArt = reponse.idArt
        WHERE article.idArt = ?");
    $resultat->execute([$_GET['art']]);
    while($ligne = $resultat->fetch()) {
    ?>
        <div class="article">
            <p>
                <?php 
                // Assurer que $nomArt est défini avant de l'utiliser
                if (!isset($nomArt) || $nomArt != $ligne['contenuArt']) { ?>
                    <p>
                        <?php echo $ligne['titreArt']." :<br>"; ?>
                        <?php echo $ligne['contenuArt']."<br>"; ?>
                    </p>
                <?php } 
                $nomArt = $ligne['contenuArt']; ?>
                <?php echo $ligne['contenuRep']."<br>"; ?>
            </p>

            <?php 
            $Memb = $cnn->prepare("SELECT nomMemb, prenomMemb, typeMemb, dateRep, membre.idMemb 
                FROM membre 
                INNER JOIN reponse ON membre.idMemb = reponse.idMemb 
                WHERE reponse.idRep = ?");
            $Memb->execute([$ligne['idRep']]);
            $repond = $Memb->fetch(); 
            ?>

            <div class="ppUtilisateur">
                <?php echo substr($repond['nomMemb'], 0, 1); ?>
            </div>

            <div class="infosdroite">
                <?php 
                if ($repond['typeMemb'] == 2) {
                    echo "<p style='color: black;'>" . "par " . $repond['nomMemb']. "</p>";
                } elseif ($repond['typeMemb'] == 1) {
                    echo "<p style='color: blue;'>" . "par " . $repond['nomMemb']. "</p>";
                } elseif ($repond['typeMemb'] == 0) {
                    echo "<p style='color: red;'>" . "par " . $repond['nomMemb']. "</p>";
                }
                ?>
                
                <?php echo $repond['dateRep']; ?>
            </div>
            
                <?php 
                // Vérification du rôle d'administrateur
                if (isset($_SESSION['typeMemb']) && $_SESSION['typeMemb'] == 0): ?>
                    <form method="POST">
                        <?php $idPromotion = $repond['idMemb'];
                        if ($repond['typeMemb'] > 0)
                        { ?>
                            <button type="submit" name="promotion" value="0">Promouvoir Admin</button>
                      <?php if ($repond['typeMemb'] > 1)
                            { ?>
                                <button type="submit" name="promotion" value="1">Promouvoir Modérateur</button>
                      <?php }
                        } ?>
                    </form>
                <?php endif; ?>
            </div>

            <?php
            // Affichage du bouton supprimer si l'utilisateur est admin ou modérateur
            if (isset($_SESSION['typeMemb']) && ($_SESSION['typeMemb'] == 0 || $_SESSION['typeMemb'] == 1)) { ?>
                <form action="#" method="POST">
                    <button type="submit" name="BtnSupprimer" id="BtnSupprimer" value="<?php echo $ligne['idRep']; ?>">Supprimer</button>
                </form>
            <?php }

            if (!empty($_POST['BtnSupprimer'])) {
                try {
                    $sql = "DELETE FROM reponse WHERE idRep=?";
                    $stmt = $cnn->prepare($sql);
                    $stmt->execute([$_POST['BtnSupprimer']]);
                    echo "Commentaire supprimé avec succès.";
                } catch (PDOException $e) {
                    echo "Erreur lors de la suppression du commentaire : " . $e->getMessage();
                }
            }
            ?>
        </div>
    <?php } ?>

    <?php if (isset($_SESSION['nomMemb'])): ?>
        <form action="#" method="POST">
        <textarea name="reponse" id="reponse" placeholder="Votre réponse" oninput="autoResize(this)"></textarea>
        <button type="submit" name="BtnReponse" id="BtnReponse">Répondre</button>
        </form>
    <?php else: ?>
        Veuillez vous connecter pour ajouter une réponse.
    <?php endif; ?>

    <?php
    if (!empty($_POST['reponse']) && isset($_SESSION['idMemb'])) {
        $article = $_GET['art'];
        $membre = $_SESSION['idMemb'];
        $contenuRep = $_POST['reponse'];

        try {
            $sql = "INSERT INTO reponse (idMemb, idArt, contenuRep) VALUES (?, ?, ?)";
            $stmt = $cnn->prepare($sql);
            $stmt->execute([$membre, $article, $contenuRep]);
            echo "Réponse ajoutée avec succès.";
        } catch (PDOException $e) {
            echo "Erreur lors de l'ajout de la réponse : " . $e->getMessage();
        }
    }

    // === TRAITEMENT DE PROMOTION ===

    if (isset($idPromotion)) {

        if (isset($_POST['promotion'])) {
            try {
                // Mise à jour du type d'utilisateur
                $stmt = $cnn->prepare("UPDATE membre SET typeMemb = ". intval($_POST['promotion']) ." WHERE idMemb = '$idPromotion'");
                $stmt->execute();
                echo "<p style='color:green;'>Utilisateur promu avec succès.</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red;'>Erreur lors de la promotion : " . $e->getMessage() . "</p>";
            }
        }
    }
    ?>

    <!-- Statistiques -->
    <?php  
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $nbArt = 0;
    $nbMemb = 0;
    $nbRep = 0;
    $resultat->closeCursor();

    $stat = $cnn->prepare("SELECT COUNT(*) FROM article");
    $stat->execute();
    $nbArt = $stat->fetchColumn();

    $stat = $cnn->prepare("SELECT COUNT(*) FROM reponse");
    $stat->execute();
    $nbRep = $stat->fetchColumn();
    ?>

    <div class="statForum">
        <p>Statistiques des forums</p>
        <?php echo $nbArt . " articles dans le forum"; ?>
        <div class="forum">
            <?php echo $nbArt + $nbRep . " messages dans le forum"; ?>
        </div>

        <?php 
        $stat = $cnn->prepare("SELECT * FROM membre");
        $stat->execute();
        while ($ligne = $stat->fetch()) {
            $nbMemb++;
        } 
        ?>
    </div>

    <div class="statMembre">
        <p>Statistiques des membres</p>
        <?php echo $nbMemb . " membres du forum"; ?>
        <?php 
        $stat = $cnn->prepare("SELECT * FROM membre WHERE EXISTS (SELECT * FROM membre HAVING MAX(membre.dateIns) = membre.dateIns)");
        $stat->execute();
        $ligne = $stat->fetch();
        ?>
        <div class="membre">
            <?php echo "MEMBRE LE PLUS RÉCENT : ".$ligne['nomMemb']. " " ; ?><br>
            <?php 
                $dateInscription = new DateTime($ligne['dateIns']);
                $dateInscription->modify('+1 hour');
                echo " inscription le " . $dateInscription->format('Y-m-d H:i:s') . " "; 
            ?>
        </div>
    </div>
</body>
</html>

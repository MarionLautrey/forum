<?php
session_start(); // Démarre la session

// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['prenomMemb'])) {
    $prenomUtilisateur = $_SESSION['prenomMemb'];
} else {
    $prenomUtilisateur = "Invité";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Accueil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">Forum</a>
        <span>Bienvenue, <?php echo htmlspecialchars($prenomUtilisateur) ?> !</span>
        <div class="auth-buttons">
            <?php if ($prenomUtilisateur == "Invité"): ?>
                <button onclick="window.location.href = 'connexion.php'">Connexion</button>
            <?php else: ?>
                <button onclick="window.location.href = 'deconnexion.php'">Déconnexion</button>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <div class="forum-container">
            <?php 
            include('connexionSQL.php');
            //requête SQL pour afficher ce qui est nécessaire dans la page d'accueil
            $resultat = $cnn->prepare("SELECT nomCat, nomRub, rubrique.idRub ,descRub, nomMemb, prenomMemb, typeMemb, contenuArt, contenuRep 
                                       FROM categorie 
                                       LEFT OUTER JOIN rubrique ON categorie.idCat=rubrique.idCat
                                       LEFT OUTER JOIN article ON rubrique.idRub=article.idRub
                                       LEFT OUTER JOIN membre ON article.idMemb=membre.idMemb
                                       LEFT OUTER JOIN reponse ON article.idArt=reponse.idArt
                                       ORDER BY categorie.idCat, rubrique.idRub");
            $resultat->execute();

            $Theme = "";
            $Rubrique = ""; // Ajouter cette variable pour éviter la duplication de div
            while ($ligne = $resultat->fetch()) {
                // Vérifier si c'est une nouvelle catégorie
                if ($Theme != $ligne['nomCat']) {
                    echo "<h2>" . $ligne['nomCat'] . "</h2>";
                    $Theme = $ligne['nomCat']; // Mettre à jour la catégorie
                }

                // Vérifier si c'est une nouvelle rubrique
                if ($Rubrique != $ligne['nomRub']) {
                    echo '<div class="rubrique">';
                    echo '<h3><a href="rubrique.php?rub=' . $ligne['idRub'] . '">' . $ligne['nomRub'] . '</a></h3>';
                    echo '<p class="description">' . $ligne['descRub'] . '</p>';
                    
                    // Sélectionner l'article le plus récent dans la rubrique
                    $Memb = $cnn->prepare("SELECT titreArt, contenuArt, membre.idMemb, nomMemb, prenomMemb, typeMemb
                                           FROM membre
                                           INNER JOIN article ON membre.idMemb=article.idMemb    
                                           WHERE EXISTS (SELECT * FROM membre 
                                                         INNER JOIN article ON membre.idMemb=article.idMemb 
                                                         INNER JOIN rubrique ON article.idRub=rubrique.idRub 
                                                         WHERE rubrique.idRub = ".$ligne['idRub']." 
                                                         HAVING MAX(dateArt) = dateArt)");
                    $Memb->execute();
                    $recent = $Memb->fetch(); 
                    
                    // Afficher l'article récent
                    echo '<div class="article-header">';
                    echo '<div class="ppUtilisateur">' . substr($recent['nomMemb'], 0, 1) . '</div>';
                    echo '<div class="dernierArticle">' . $recent['titreArt'] . '</div>';
                    echo '</div>';

                    // Afficher le nom de l'utilisateur
                    echo '<div class="infosDroite">';
                    if (isset($recent['idMemb']))
                    {
                        if ($recent['typeMemb'] == 2) {
                            echo '<p style="color: black;">' . "par " . $recent['nomMemb'] . '</p>';
                        } elseif ($recent['typeMemb'] == 1) {
                            echo '<p style="color: blue;">' . "par " . $recent['nomMemb'] . '</p>';
                        } elseif ($recent['typeMemb'] == 0) {
                            echo '<p style="color: red;">' . "par " . $recent['nomMemb'] . '</p>';
                        }
                    }

                    // Afficher la date du dernier message
                    // Affichage regroupé nom + date

                    $Mess = $cnn->prepare("SELECT article.titreArt, membre.nomMemb, membre.prenomMemb, date 
                        FROM (
                            SELECT idArticle, idMemb, date 
                            FROM (
                                SELECT article.idArt AS idArticle, article.idMemb AS idMemb, article.dateArt AS date 
                                FROM article 
                                WHERE article.idRub = ".$ligne['idRub']."
                                UNION 
                                SELECT reponse.idArt AS idArticle, reponse.idMemb AS idMemb, reponse.dateRep AS date 
                                FROM reponse 
                                INNER JOIN article ON reponse.idArt = article.idArt 
                                WHERE reponse.idArt IN (SELECT article.idArt FROM article WHERE article.idRub = ".$ligne['idRub'].")
                            ) AS combined_results 
                            ORDER BY date DESC LIMIT 1
                        ) AS combined_results 
                        INNER JOIN article ON combined_results.idArticle = article.idArt 
                        INNER JOIN membre ON combined_results.idMemb = membre.idMemb");
                    $Mess->execute();
                    $dernier = $Mess->fetch(); 

                    echo '<p class="dernMess">' . $dernier['date'] . '</p>';
                    echo '</div>'; // fin infosDroite

                    // Compter les messages dans cette rubrique
                    $resultatCount = $cnn->prepare("SELECT 
                        (SELECT COUNT(contenuArt) FROM article 
                         INNER JOIN rubrique ON article.idRub=rubrique.idRub 
                         WHERE rubrique.idRub = :idRubrique) as countArt, 
                        (SELECT COUNT(contenuRep) FROM reponse 
                         INNER JOIN article ON reponse.idArt=article.idArt 
                         INNER JOIN rubrique ON article.idRub=rubrique.idRub 
                         WHERE rubrique.idRub = :idReponse) as countRep");
                    $resultatCount->execute(array('idRubrique'=>$ligne['idRub'], 'idReponse'=>$ligne['idRub']));
                    $messages = $resultatCount->fetch();
                    if (($messages['countArt'] + $messages['countRep']) > 1)
                    {
                        echo '<div class="NombreMessage">' . ($messages['countArt'] + $messages['countRep']) . " messages" . '</div>';
                    }
                    else
                    {
                        echo '<div class="NombreMessage">' . ($messages['countArt'] + $messages['countRep']) . " message" . '</div>';
                    }
                    
                    echo '</div>'; // Fin de la div rubrique

                    $Rubrique = $ligne['nomRub']; // Mettre à jour la rubrique
                    $message=$ligne['idRub'];

                    if($_SESSION['typeMemb'] == 0 && isset($_SESSION['typeMemb']))
                    { 
                    ?>
                        <form action="#" method="POST">
                            <input type="text" name="NomRubModif" id="NomRubModif" placeholder="Nom de la rubrique">
                            <input type="text" name="DescRubModif" id="DescRubModif" placeholder="Description de la rubrique">
                            <input type="text" name="NomCatModif" id="NomCatModif" placeholder="Catégorie de la rubrique">
                            <button type="submit" name="BtnModifier" id="BtnModifier" value="<?php echo $ligne['idRub'] ?>" >Modifier la rubrique</button>
                        </form>
                    <?php
                    }
                    $idRubModif = $_POST['BtnModifier'];
                    $nomRubModif = $_POST['NomRubModif'];
                    $descRubModif = $_POST['DescRubModif'];
                    $nomCatModif = $_POST['NomCatModif'];
                    
                    if (!empty($nomRubModif)) {
                        try {
                            $sql = "UPDATE rubrique SET nomRub = '".$nomRubModif."'  WHERE idRub = ".$idRubModif."";
                            $stmt = $cnn->prepare($sql);
                            $stmt->execute();
                            echo "Rubrique modifier avec succès.";
                        } catch (PDOException $e) {
                            echo "Erreur lors de la modification de la rubrique : " . $e->getMessage();
                        }
                    }

                    if (!empty($descRubModif)) {
                        try {
                            $sql = "UPDATE rubrique SET descRub = '".$descRubModif."'  WHERE idRub = ".$idRubModif."";
                            $stmt = $cnn->prepare($sql);
                            $stmt->execute();
                            echo "Rubrique modifier avec succès.";
                        } catch (PDOException $e) {
                            echo "Erreur lors de la modification de la rubrique : " . $e->getMessage();
                        }   
                    }

                    if (!empty($nomCatModif)) {
                        try {
                            $sql = "UPDATE rubrique SET idCat = (SELECT idCat FROM categorie WHERE nomCat = '".$nomCatModif."')  WHERE idRub = ".$idRubModif."";
                            $stmt = $cnn->prepare($sql);
                            $stmt->execute();
                            echo "Rubrique modifier avec succès.";
                        } catch (PDOException $e) {
                            echo "Erreur lors de la modification de la rubrique : " . $e->getMessage();
                        }  
                    }

                    $message=$ligne['idRub'];

                    // supprimer une rubrique
                    if($_SESSION['typeMemb'] == 0 && isset($_SESSION['typeMemb']))
                      { 
                        ?>
                          <form action="#" method="POST">
                            <button type="submit" name="BtnSupprimer" id="BtnSupprimer" value="<?php echo $ligne['idRub']; ?>">Supprimer la rubrique</button>
                          </form>
                        <?php
                      }
                      $idSuppr = $_POST['BtnSupprimer'];
                                
                      if (!empty($idSuppr)) {
                        try {
                            $sql = "DELETE FROM rubrique WHERE idRub=".$idSuppr."";
                            $stmt = $cnn->prepare($sql);
                            $stmt->execute();
                            echo "Rubrique supprimé avec succès.";
                        } catch (PDOException $e) {
                            echo "Erreur lors de la suppression de la rubrique : " . $e->getMessage();
                        }
                      }
                    $message=$ligne['idRub'];
                }                
            } ?>
            
            <br>
            <br>
            
            <?php 
            // ajouter une rubrique
            if($_SESSION['typeMemb'] == 0 && isset($_SESSION['typeMemb']))
            { 
              ?>
                <form action="#" method="POST">
                    <input type="text" name="NomRubAjout" id="NomRubAjout" placeholder="Nom de la rubrique">
                    <input type="text" name="DescRubAjout" id="DescRubAjout" placeholder="Description de la rubrique">
                    <input type="text" name="NomCatAjout" id="NomCatAjout" placeholder="Catégorie de la rubrique">
                    <button type="submit" name="BtnAjouter" id="BtnAjouter" >Ajouter une rubrique</button>
                </form>
              <?php
            }
            $NomRubAjout = $_POST['NomRubAjout'];
            $DescRubAjout = $_POST['DescRubAjout'];
            $NomCatAjout = $_POST['NomCatAjout'];
                        
            if (!empty($NomRubAjout) && !empty($NomCatAjout)) {
              try {
                  $sql = "INSERT INTO rubrique(nomRub, descRub, idCat) VALUES('".$NomRubAjout."', '".$DescRubAjout."', (SELECT idCat FROM categorie WHERE nomCat = '".$NomCatAjout."'))";
                  $stmt = $cnn->prepare($sql);
                  $stmt->execute();
                  echo "Rubrique ajouter avec succès.";
              } catch (PDOException $e) {
                  echo "Erreur lors de l'ajout de la rubrique : " . $e->getMessage();
              }
            }           
            ?>
        </div>
    </main>

    <?php 
    // Statistiques des articles, réponses et membres
    $nbArt = 0;
    $nbMemb = 0;
    $nbRep = 0;

    $stat = $cnn->prepare("SELECT * FROM article");
    $stat->execute();
    while ($ligne = $stat->fetch()) {
        $nbArt++;
    } 

    $stat = $cnn->prepare("SELECT * FROM reponse");
    $stat->execute();
    while ($ligne = $stat->fetch()) {
        $nbRep++;
    } 
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
            <?php echo "MEMBRE LE PLUS RÉCENT ".$ligne['nomMemb']. " " ; ?><br>
            <?php 
                $dateInscription = new DateTime($ligne['dateIns']);
                $dateInscription->modify('+1 hour');
                echo " inscription en " . $dateInscription->format('Y-m-d H:i:s') . " "; 
            ?>
        </div>
    </div>
</body>
</html>

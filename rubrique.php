<?php 
session_start(); // Démarrer la session
include('connexionSQL.php'); // Connexion à la base de données

// Vérifier si l'utilisateur est connecté et récupérer son prénom
if (isset($_SESSION['prenomMemb'])) {
    $prenomUtilisateur = $_SESSION['prenomMemb']; // Utiliser le prénom
} else {
    $prenomUtilisateur = "Invité"; // Si personne n'est connecté
}
?>
      <?php
        // Définir le nouveau fuseau horaire
        date_default_timezone_set('UTC');
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" />
    <title>Forum rubrique </title>
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
      <?php 
        $resultat = $cnn->prepare("SELECT article.idArt, titreArt, contenuArt, membre.idMemb, nomMemb, prenomMemb, typeMemb, rubrique.idRub, nomRub FROM rubrique LEFT OUTER JOIN article ON rubrique.idRub=article.idRub
                                                                                                                                                   LEFT OUTER JOIN membre ON article.idMemb=membre.idMemb
                                                                                                                                                   LEFT OUTER JOIN reponse ON article.idArt=reponse.idArt
                                   WHERE rubrique.idRub = ".$_GET['rub']."
                                   ORDER BY rubrique.idRub");
        $resultat->execute();
        while($ligne = $resultat->fetch()){
          ?>
          <div class="page-rubrique">
            <div class="page-rubrique-sub">
              <?php if ($rubri != $ligne['idRub']) { ?>
              <p>
                <?php echo $ligne['nomRub']." :\n"; ?>
              </p>
              <?php } 
              $rubri=$ligne['idRub'] ?>
            </div>
            <div class="TitreArt">
            <?php if ($art != $ligne['idArt']) { ?>
              <p>
                <a href="http://srvdocker:8013/article.php?art=<?php echo $ligne['idArt']; ?>"><?php echo $ligne['titreArt']."\n"; ?></a>
              <p>
            <?php } 
            $art=$ligne['idArt'] ?>
            </div>
            <?php if ($message != $ligne['idArt']) { ?>
                      <div class="ppUtilisateur">
                          <?php echo substr($ligne['nomMemb'], 0, 1) ?>
                      </div>
                      <div class="infosDroite">
                  <?php if ($ligne['typeMemb'] == 2) 
                      { ?>
                          <p style="color: <?php echo "black"; ?>;">
                              <?php echo "par ".$ligne['nomMemb']."\n" ?>
                          </p>
              <?php }
                      if ($ligne['typeMemb'] == 1) 
                      { ?>
                          <p style="color: <?php echo "blue"; ?>;">
                              <?php echo "par ".$ligne['nomMemb']."\n" ?>
                          </p>
              <?php }
                      if ($ligne['typeMemb'] == 0) 
                      { ?>
                          <p style="color: <?php echo "red"; ?>;">
                              <?php echo "par ".$ligne['nomMemb']."\n" ?>
                          </p>
              <?php } ?>
             
            </div>
            <div class="MessageRecent-rubique">
              <?php $MessageRecent = $cnn->prepare("SELECT dateRep 
                                                    FROM reponse
                                                    WHERE EXISTS (SELECT * FROM reponse INNER JOIN article ON reponse.idArt=article.idArt WHERE article.idArt = ".$ligne['idArt']." HAVING MAX(reponse.dateRep) = reponse.dateRep)");
              $MessageRecent->execute();
              $recent = $MessageRecent->fetch();
              echo $recent['dateRep']; ?>
            </div>
            <div class="infosDroite">
              <?php 
                $resultatCount = $cnn->prepare("SELECT COUNT(contenuRep) as countRep
                                                FROM reponse INNER JOIN article ON reponse.idArt=article.idArt
                                                WHERE article.idArt = ".$ligne['idArt']."");
                $resultatCount->execute();
                $messages = $resultatCount->fetch();
                echo $messages['countRep'];
                if ( $messages['countRep'] <= 1) 
                {
                    echo " message";
                }
                else 
                { 
                    echo " messages";
                }
              ?>
            </div>
            <?php
            if($_SESSION['typeMemb'] == 0 && isset($_SESSION['typeMemb'] ))
              { 
                ?>  
                  <form action="#" method="POST">
                    <button type="submit" name="BtnSupprimerUtil" id="BtnSupprimerUtil" value="<?php echo $ligne['idMemb']; ?>">Supprimer l'utilisateur</button>
                  </form>
                <?php
              }
              $memb = $_POST['BtnSupprimerUtil']; 
              if (!empty($memb)) {
                try {
                    $sql = "DELETE FROM membre WHERE membre.idMemb='".$memb."'";
                    $stmt = $cnn->prepare($sql);
                    $stmt->execute();
                    echo "Utilisateur supprimé avec succès.";
                } catch (PDOException $e) {
                    echo "Erreur lors de la suppression de l'utilisateur : ".$e->getMessage()."";
                }
              }
            ?>
            <?php
            if(($_SESSION['typeMemb'] == 0 || $_SESSION['typeMemb'] == 1) && isset($_SESSION['typeMemb'] ))
              { 
                ?>  
                  <form action="#" method="POST">
                    <button type="submit" name="BtnSupprimer" id="BtnSupprimer" value="<?php echo $ligne['idArt']; ?>">Supprimer l'article</button>
                  </form>
                <?php
              }
              $art = $_POST['BtnSupprimer'];
                
              if (!empty($art)) {
                try {
                    $sql = "DELETE FROM article WHERE idArt=".$art."";
                    $stmt = $cnn->prepare($sql);
                    $stmt->execute();
                    echo "Article supprimé avec succès.";
                } catch (PDOException $e) {
                    echo "Erreur lors de la suppression de l'article : " . $e->getMessage();
                }
              }
            } 
            $message=$ligne['idArt'];
            ?>
          </div> 
          <?php
        }

if (isset($_SESSION['nomMemb'])) {
  ?>

    <form action="#" method="POST" class="form-ajout-article">
    <input type="text" name="titre" id="titre" placeholder="Titre">
    <input type="text" name="contenu" id="contenu" placeholder="Contenu">
    <button type="submit" name="BtnAjouter" id="BtnAjouter">Ajouter un article</button>
  </form>
  <?php
} else {
  // L'utilisateur n'est pas connecté, affichez un message
  echo 'Veuillez vous connecter pour ajouter un article.';
}

$titre = $_POST['titre'] ?? '';
$contenu = $_POST['contenu'] ?? '';
$membre = $_SESSION['idMemb']?? '';
$rubrique = $_GET['rub']?? '';

if (!empty($titre) && !empty($contenu) && !empty($membre)&& !empty($rubrique)) {
  try {
      $sql = "INSERT INTO article (titreArt,contenuArt,idMemb,idRub) VALUES ('".$titre."', '".$contenu."', '".$membre."', ".$rubrique.")";
      $stmt = $cnn->prepare($sql);
      $stmt->execute();
      echo "Article ajouter avec succès.";
  } catch (PDOException $e) {
      echo "Erreur lors de l'ajout de l'article : " . $e->getMessage();
  }
}
?>
    </body>
    <?php  
    $nbArt = 0;
    $nbMemb = 0;
    $nbRep = 0;

    $stat = $cnn->prepare("SELECT * FROM article");
    $stat->execute();

    while ($ligne = $stat->fetch()) {
        $nbArt += 1;
    } 
    
    $stat = $cnn->prepare("SELECT * FROM reponse");
    $stat->execute();

    while ($ligne = $stat->fetch()) {
        $nbRep += 1;
    } ?>

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
</html>
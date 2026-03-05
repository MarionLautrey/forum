<?php
session_start(); // Démarre la session


// Inclusion du fichier de connexion à la base de données
include('connexionSQL.php');

// Vérifier si le formulaire de connexion a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';  // L'email correspond à idMemb
    $password = $_POST['psw'] ?? ''; // Mot de passe

    // Vérifier que les champs sont remplis
    if (!empty($email) && !empty($password)) {
        try {
            // Préparer la requête pour vérifier les informations de l'utilisateur
            $stmt = $cnn->prepare("SELECT * FROM membre WHERE idMemb = :email AND mdpMemb = :psw");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':psw', $password, PDO::PARAM_STR);

            // Exécuter la requête
            $stmt->execute();

            // Vérifier si l'utilisateur existe
            if ($stmt->rowCount() > 0) {
                // L'utilisateur existe, récupérer le prénom
                $user = $stmt->fetch(); // Récupère les données de l'utilisateur
                $id = $user['idMemb'];
                $prenom = $user['prenomMemb']; // Récupérer le prénom de l'utilisateur
                $type = $user['typeMemb'];

                // Démarrer la session et stocker les informations
                $_SESSION['idMemb'] = $id;
                $_SESSION['nomMemb'] = $email;  // Utiliser l'email comme identifiant
                $_SESSION['prenomMemb'] = $prenom; // Stocker le prénom de l'utilisateur
                $_SESSION['typeMemb'] = $type;
                
                header('Location: index.php');  // Rediriger vers la page d'accueil
                exit();
            } else {
                // L'email ou le mot de passe est incorrect
                echo "<p style='color: red;'>Identifiants incorrects. Essayez à nouveau.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Erreur SQL : " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Veuillez remplir tous les champs.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="background"></div>
    <div class="content">
        <div class="form-container">
            <h1>Se connecter</h1>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="psw">Mot de passe</label>
                    <input type="password" id="psw" name="psw" required>
                </div>
                <div class="form-group">
                    <button type="submit">Se connecter</button>
                </div>
            </form>
            <div class="links">
                <p>Pas encore inscrit ? <a href="inscription.php">S'inscrire</a></p>
                <p><a href="index.php">Retour à l'accueil</a></p>
            </div>
        </div>
    </div>
</body>
</html>

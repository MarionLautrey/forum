    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
    <?php include('connexionSQL.php'); 
        // Vérifier si le formulaire a été soumis avec la méthode POST
        if ($_SERVER['REQUEST_METHOD'] ==='POST') {
            // Récupérer les valeurs des champs du formulaire
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $password = $_POST['psw'];

            // Validation de base : vérifier que tous les champs sont remplis
            if (!empty($nom) && !empty($prenom) && !empty($password)) {
                // Préparation de la requête d'insertion dans la base de données
                $stmt = $cnn->prepare("INSERT INTO membre (nomMemb, prenomMemb, mdpMemb) VALUES (:nom, :prenom, :psw);");

                // Lier les variables PHP aux paramètres de la requête
                $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);    // Associe le nom à :nom
                $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);  // Associe le prénom à :prenom
                $stmt->bindParam(':psw', $password, PDO::PARAM_STR);   // Associe le mot de passe à :psw

                // Exécuter la requête et vérifier si l'insertion a réussi
                if ($stmt->execute()) {
                    // Si l'insertion réussit, afficher un message de succès
                    echo "<p>Compte créé avec succès. Vous pouvez maintenant vous connecter.</p>";
                } else {
                    // Si l'insertion échoue, afficher un message d'erreur
                    echo "<p style='color: red;'>Une erreur est survenue. Veuillez réessayer.</p>";
                }
            } else {
                // Si un champ est vide, afficher un message d'erreur
                echo "<p style='color: red;'>Veuillez remplir tous les champs.</p>";
            }
        }
    ?>
    </body>
    </html>
    
<template>
  <nav class="header-nav">
    <a href="/homePage" class="nav-link">Accueil</a>
    <a href="/createQuizz" class="nav-link">Créer Quizz</a>
    <a href="/stats" class="nav-link">Stats</a>

    <a href="#" class="nav-link" @click="logout">Déconnexion</a>
  </nav>
</template>

<script>
export default {
  methods: {
    logout() {
      // Supprimer le cookie userId
      document.cookie = 'userId=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';

      // Supprimer le cookie authToken
      document.cookie = 'authToken=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
      
      // Rediriger vers la page de connexion
      window.location.href = '/login';
    }
  }
}
</script>

<style>
.header-nav {
  background-color: #f5f5f5;
  padding: 10px 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  position: sticky; /* Rendre le header sticky */
  top: 0; /* Fixer le header en haut de la fenêtre */
  z-index: 1000; /* Assurer que le header est au-dessus de tout autre contenu */
}

.nav-link {
  margin-right: 20px;
  padding: 10px 15px;
  text-decoration: none;
  color: #333;
  font-weight: bold;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.nav-link:hover {
  background-color: #e1e1e1;
}
</style>

<template>
  <div class="login-container">
      <h2 class="login-title">Connexion</h2>
      <form @submit.prevent="login" class="login-form">
        <input type="hidden" name="csrf_token" :value="csrfToken">

          <div class="form-group">
              <label for="email" class="form-label">Email :</label>
              <input id="email" v-model="email" type="email" class="form-input" required>
          </div>
          <div class="form-group">
              <label for="password" class="form-label">Mot de passe :</label>
              <input id="password" v-model="password" type="password" class="form-input" required>
          </div>
          <div class="button-container">
            <button type="submit" class="btn btn-primary">Se connecter</button>
            <a href="/register" class="btn btn-secondary">Créer un compte</a>
          </div>
      </form>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  data() {
      return {
          email: '',
          password: '',
          csrfToken: '',
          action:'' 
      };
  },
  mounted() {
    // Récupérez le jeton CSRF lors du chargement du composant
    this.fetchCsrfToken();
  },
  methods: {
      async fetchCsrfToken() {
        // Effectuez une requête pour récupérer le jeton CSRF du serveur
         const uniqueId = 'login' + Math.random().toString(36).substr(2, 9);

        try {
          const response = await axios.get('http://192.168.90.3/api/csrf-token/' + uniqueId);
          this.csrfToken = response.data.csrfToken; 
          this.action = uniqueId; 

        } catch (error) {
          console.error('Erreur lors de la récupération du jeton CSRF :', error);
        }
      },
      async login() {
          try {
              const response = await axios.post('http://192.168.90.3/api/login', {
                  email: this.email,
                  password: this.password,
                  csrf_token: this.csrfToken,
                  action: this.action,

              });
              document.cookie = `authToken=${response.data.token};path=/;max-age=1800`;
              document.cookie = `userId=${response.data.user.id};path=/`;
              window.location.href = '/homePage';
          } catch (error) {
              if (error.response && error.response.status === 401) {
                  Swal.fire({
                      title: 'Erreur!',
                      text: error.response.data.message,
                      icon: 'error',
                      confirmButtonText: 'OK'
                  });
              } else {
                  Swal.fire({
                      title: 'Erreur!',
                      text: 'Une erreur est survenue. Veuillez réessayer.',
                      icon: 'error',
                      confirmButtonText: 'OK'
                  });
              }
          }
      }
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  align-content: flex-start;
  flex-wrap: nowrap;
  flex-direction: column;
  margin: auto;
}

.login-title {
  margin-bottom: 20px;
}

.login-form {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 5px;
  width: 400px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 20px;
}

.btn {
  display: inline-block;
  padding: 8px 16px;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
}
.button-container {
  display: flex;
  justify-content: space-between;
}
.btn-primary {
  background-color: #007bff;
  color: #fff;
}

.btn-secondary {
  background-color: #6c757d;
  color: #fff;
}

.btn:hover {
  opacity: 0.8;
}
</style>

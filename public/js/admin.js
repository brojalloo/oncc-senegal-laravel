// public/js/admin.js - Pour les boutons d'actions
$(document).ready(function() {
    // Export des données
    $('#exportData').click(function() {
        window.location.href = 'index.php?action=export_data';
    });
    
    // Sauvegarde BDD
    $('#backupDB').click(function() {
        if(confirm('Lancer la sauvegarde de la base de données ?')) {
            $.post('index.php?action=backup_database', function(data) {
                alert('Sauvegarde terminée : ' + data.filename);
            });
        }
    });
});
// public/js/admin.js
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la suppression des utilisateurs
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-id');
            const userName = this.closest('tr').querySelector('td:nth-child(2)').textContent;
            
            if(confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${userName}" ?`)) {
                fetch(`index.php?action=user-delete&id=${userId}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Erreur lors de la suppression');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur réseau');
                });
            }
        });
    });
});
// public/js/admin.js
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la suppression avec confirmation
    const deleteButtons = document.querySelectorAll('.delete-user');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const userId = this.getAttribute('data-id');
            const userName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
            
            if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${userName}" ?`)) {
                // Redirection vers l'action de suppression
                window.location.href = `index.php?action=admin_users_delete&id=${userId}`;
            }
        });
    });
});
fetch('get_user.php')
    .then(response => {
        if (!response.ok) {
            window.location.href = 'login.html';
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.name) {
            document.querySelectorAll('#userName').forEach(element => {
                element.textContent = data.name;
            });
        }
    })
    .catch(error => {
        console.error('Error al obtener el usuario:', error);
    });
    
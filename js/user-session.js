fetch('get_user.php', {
    credentials: 'same-origin',
    cache: 'no-store'
})
.then(response => {
    if (response.status === 401) {
        window.location.href = 'login.html';
        return null;
    }

    if (!response.ok) {
        throw new Error('No se pudieron obtener los datos del usuario.');
    }

    return response.json();
})
.then(data => {
    if (!data) return;

    document.querySelectorAll('#userName').forEach(element => {
        element.textContent = data.name || 'Usuario';
    });

    document.querySelectorAll('.img-profile').forEach(img => {
        img.onerror = function () {
            this.onerror = null;
            this.src = 'img/undraw_profile.svg';
        };

        if (data.photo_url) {
            img.src = new URL(data.photo_url, document.baseURI).href;
        } else {
            img.src = 'img/undraw_profile.svg';
        }
    });
})
.catch(error => console.error('Error de sesión:', error));

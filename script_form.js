const toggle = document.getElementById('toggle');
const formParticulier = document.getElementById('form-particulier');
const formEntreprise = document.getElementById('form-entreprise');

toggle.addEventListener('click', function() {
this.classList.toggle('entreprise');
				
if (this.classList.contains('entreprise')) {
	formParticulier.classList.remove('active');
	formEntreprise.classList.add('active');
} 
else {
	formEntreprise.classList.remove('active');
	formParticulier.classList.add('active');
	}
});
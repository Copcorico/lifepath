const toggle = document.getElementById('toggle');
const formParticulier = document.getElementById('form-particulier');
const formEntreprise = document.getElementById('form-entreprise');
const statutEtudiant = document.getElementById('statut-etudiant');
const statutPilote = document.getElementById('statut-pilote');
const groupeClasse = document.getElementById('groupe-classe');
const groupePilote = document.getElementById('groupe-pilote');
const inputClasse = document.getElementById('classe');
const selectPilote = document.getElementById('pilote');

if (toggle && formParticulier && formEntreprise) {
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
}

function updateStatutFields() {
	if (!statutEtudiant || !groupeClasse || !groupePilote || !inputClasse || !selectPilote) {
		return;
	}

	const isEtudiant = statutEtudiant.checked;
	groupeClasse.hidden = !isEtudiant;
	groupePilote.hidden = !isEtudiant;
	inputClasse.required = isEtudiant;
	inputClasse.disabled = !isEtudiant;
	selectPilote.required = isEtudiant;
	selectPilote.disabled = !isEtudiant;
}

if (statutEtudiant && statutPilote) {
	if (!statutEtudiant.checked && !statutPilote.checked) {
		statutEtudiant.checked = true;
	}
	updateStatutFields();
	statutEtudiant.addEventListener('change', updateStatutFields);
	statutPilote.addEventListener('change', updateStatutFields);
}
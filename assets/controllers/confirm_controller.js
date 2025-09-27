import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = {
        title: String,
        message: String,
        confirmButtonText: String,
        cancelButtonText: String,
        icon: String
    }

    onSubmit(event) {
        event.preventDefault();

        Swal.fire({
            title: this.hasTitleValue ? this.titleValue : 'Êtes-vous sûr ?',
            text: this.hasMessageValue ? this.messageValue : "Cette action est irréversible !",
            icon: this.hasIconValue ? this.iconValue : 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: this.hasConfirmButtonTextValue ? this.confirmButtonTextValue : 'Oui, supprimer !',
            cancelButtonText: this.hasCancelButtonTextValue ? this.cancelButtonTextValue : 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                this.element.submit();
            }
        });
    }
}
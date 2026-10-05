import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal', 'form', 'name', 'token'];

    connect() {
        this.close = this.close.bind(this);
    }

    open(event) {
        const button = event.currentTarget;
        const url = button.dataset.url;
        const name = button.dataset.name;
        const token = button.dataset.token;

        this.formTarget.action = url;
        this.nameTarget.textContent = name;
        this.tokenTarget.value = token;

        this.modalTarget.showModal();
    }

    close() {
        this.modalTarget.close();
    }

    handleBackdropClick(event) {
        if (event.target === this.modalTarget) {
            this.close();
        }
    }
}

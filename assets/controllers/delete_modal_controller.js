import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal', 'form', 'name', 'token'];

    open(event) {
        const button = event.currentTarget;

        this.formTarget.action = button.dataset.url;
        this.nameTarget.textContent = button.dataset.name;
        this.tokenTarget.value = button.dataset.token;

        this.modalTarget.showModal();
    }

    close() {
        this.modalTarget.close();
    }
}

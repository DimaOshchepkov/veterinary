import './stimulus_bootstrap.js';
import { registerReactControllerComponents } from '@symfony/ux-react';
import * as Turbo from '@hotwired/turbo';

import * as AppointmentFormModule from '@/react/controllers/AppointmentForm';

console.log('🔍 AppointmentFormModule:', AppointmentFormModule);
console.log('🔍 AppointmentFormModule.default:', AppointmentFormModule.default);

Turbo.start();

registerReactControllerComponents({
    './AppointmentForm.tsx': AppointmentFormModule,
});

console.log('✅ Компоненты зарегистрированы');

export interface Option {
    id: number;
    label: string
}

export type Location = 'salon' | 'home';

export interface AppointmentFormData {
    pet: string;
    service: string;
    location: Location;
    address?: string;
    date: string;
    time: string;
}

import type {ReactNode} from 'react';
import api from '@/utils/api';
import {FieldValues, Path, UseFormSetError, useForm, type SubmitHandler} from 'react-hook-form';


export const inputClass =
    'block w-full rounded-md border border-gray-300 px-3 py-2.5 shadow-sm transition-colors ' +
    'focus:border-gray-900 focus:ring-gray-900 sm:text-sm';

export function Field({label, error, children}: { label: string; error?: string; children: ReactNode }) {
    return (
        <label className="block">
            <span className="mb-1.5 block text-sm font-medium text-gray-700">{label}</span>
            {children}
            {error && <p className="mt-1.5 text-xs text-red-500">{error}</p>}
        </label>
    );
}


export function applyServerErrors<T extends FieldValues>(e: any, setError: UseFormSetError<T>) {
    const violations = e?.response?.status === 422 ? e.response.data?.violations : null;

    if (!Array.isArray(violations)) {
        setError('root', {message: 'Не удалось отправить заявку. Попробуйте позже.'});
        return;
    }
    for (const v of violations) {
        setError((v.propertyPath || 'root') as Path<T>, {message: v.title});
    }
}


const LOCATIONS = [
    {value: 'salon', label: 'В салоне'},
    {value: 'home', label: 'На дому'},
] as const;

const todayLocal = () => {
    const d = new Date();
    return new Date(d.getTime() - d.getTimezoneOffset() * 60_000).toISOString().slice(0, 10);
};

export default function AppointmentForm({pets, services}: { pets: Option[]; services: Option[] }) {
    const {
        register, handleSubmit, watch, setError,
        formState: {errors, isSubmitting, isSubmitSuccessful},
    } = useForm<AppointmentFormData>({
        defaultValues: {location: 'salon'},
        shouldUnregister: true,
    });

    const location = watch('location');

    const onSubmit: SubmitHandler<AppointmentFormData> = async (data) => {
        try {
            const response = await api.post('/appointments', {
                ...data,
                pet: Number(data.pet),
                service: Number(data.service),
            });
            const responseData = response?.data ?? response;

            if (responseData?.redirectUrl) {
                window.location.href = response.data.redirectUrl;
            }
        } catch (e) {
            applyServerErrors(e, setError);
        }
    };

    if (isSubmitSuccessful) {
        return <p className="text-sm text-gray-700">Заявка создана.</p>;
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5" noValidate>
            <Field label="Питомец" error={errors.pet?.message}>
                <select {...register('pet', {required: 'Выберите питомца'})} className={inputClass}>
                    <option value="">Выберите питомца</option>
                    {pets.map((p) => <option key={p.id} value={p.id}>{p.label}</option>)}
                </select>
            </Field>

            <Field label="Тип услуги" error={errors.service?.message}>
                <select {...register('service', {required: 'Выберите услугу'})} className={inputClass}>
                    <option value="">Выберите услугу</option>
                    {services.map((s) => <option key={s.id} value={s.id}>{s.label}</option>)}
                </select>
            </Field>

            <fieldset>
                <legend className="mb-1.5 text-sm font-medium text-gray-700">Место приема</legend>
                <div className="flex gap-4">
                    {LOCATIONS.map((l) => (
                        <label key={l.value} className="inline-flex cursor-pointer items-center">
                            <input
                                type="radio"
                                value={l.value}
                                {...register('location')}
                                className="h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900"
                            />
                            <span className="ml-2 text-sm text-gray-700">{l.label}</span>
                        </label>
                    ))}
                </div>
            </fieldset>

            {location === 'home' && (
                <Field label="Адрес" error={errors.address?.message}>
                    <input
                        {...register('address', {required: 'Укажите адрес', setValueAs: (v) => v.trim()})}
                        className={inputClass}
                        placeholder="ул. Примерная, д. 1"
                    />
                </Field>
            )}

            <Field label="Дата приема" error={errors.date?.message}>
                <input
                    type="date"
                    min={todayLocal()}
                    {...register('date', {
                        required: 'Укажите дату',
                        validate: (v) => v >= todayLocal() || 'Дата не может быть в прошлом',
                    })}
                    className={inputClass}
                />
            </Field>

            <Field label="Время приема" error={errors.time?.message}>
                <input type="time" {...register('time', {required: 'Укажите время'})} className={inputClass}/>
            </Field>

            {errors.root && <p className="text-sm text-red-500">{errors.root.message}</p>}

            <div className="border-t border-gray-100 pt-6">
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="rounded-md bg-gray-900 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {isSubmitting ? 'Отправка...' : 'Создать заявку'}
                </button>
            </div>
        </form>
    );
}

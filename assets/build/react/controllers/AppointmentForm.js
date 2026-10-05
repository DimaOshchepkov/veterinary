import api from '@/utils/api';
import { useForm } from 'react-hook-form';
import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
export const inputClass = 'block w-full rounded-md border border-gray-300 px-3 py-2.5 shadow-sm transition-colors ' + 'focus:border-gray-900 focus:ring-gray-900 sm:text-sm';
export function Field({
  label,
  error,
  children
}) {
  return /*#__PURE__*/_jsxs("label", {
    className: "block",
    children: [/*#__PURE__*/_jsx("span", {
      className: "mb-1.5 block text-sm font-medium text-gray-700",
      children: label
    }), children, error && /*#__PURE__*/_jsx("p", {
      className: "mt-1.5 text-xs text-red-500",
      children: error
    })]
  });
}
export function applyServerErrors(e, setError) {
  const violations = e?.response?.status === 422 ? e.response.data?.violations : null;
  if (!Array.isArray(violations)) {
    setError('root', {
      message: 'Не удалось отправить заявку. Попробуйте позже.'
    });
    return;
  }
  for (const v of violations) {
    setError(v.propertyPath || 'root', {
      message: v.title
    });
  }
}
const LOCATIONS = [{
  value: 'salon',
  label: 'В салоне'
}, {
  value: 'home',
  label: 'На дому'
}];
const todayLocal = () => {
  const d = new Date();
  return new Date(d.getTime() - d.getTimezoneOffset() * 60_000).toISOString().slice(0, 10);
};
export default function AppointmentForm({
  pets,
  services
}) {
  const {
    register,
    handleSubmit,
    watch,
    setError,
    formState: {
      errors,
      isSubmitting,
      isSubmitSuccessful
    }
  } = useForm({
    defaultValues: {
      location: 'salon'
    },
    shouldUnregister: true
  });
  const location = watch('location');
  const onSubmit = async data => {
    try {
      await api.post('/appointments', {
        ...data,
        pet: Number(data.pet),
        service: Number(data.service)
      });
    } catch (e) {
      applyServerErrors(e, setError);
    }
  };
  if (isSubmitSuccessful) {
    return /*#__PURE__*/_jsx("p", {
      className: "text-sm text-gray-700",
      children: "\u0417\u0430\u044F\u0432\u043A\u0430 \u0441\u043E\u0437\u0434\u0430\u043D\u0430."
    });
  }
  return /*#__PURE__*/_jsxs("form", {
    onSubmit: handleSubmit(onSubmit),
    className: "space-y-5",
    noValidate: true,
    children: [/*#__PURE__*/_jsx(Field, {
      label: "\u041F\u0438\u0442\u043E\u043C\u0435\u0446",
      error: errors.pet?.message,
      children: /*#__PURE__*/_jsxs("select", {
        ...register('pet', {
          required: 'Выберите питомца'
        }),
        className: inputClass,
        children: [/*#__PURE__*/_jsx("option", {
          value: "",
          children: "\u0412\u044B\u0431\u0435\u0440\u0438\u0442\u0435 \u043F\u0438\u0442\u043E\u043C\u0446\u0430"
        }), pets.map(p => /*#__PURE__*/_jsx("option", {
          value: p.id,
          children: p.label
        }, p.id))]
      })
    }), /*#__PURE__*/_jsx(Field, {
      label: "\u0422\u0438\u043F \u0443\u0441\u043B\u0443\u0433\u0438",
      error: errors.service?.message,
      children: /*#__PURE__*/_jsxs("select", {
        ...register('service', {
          required: 'Выберите услугу'
        }),
        className: inputClass,
        children: [/*#__PURE__*/_jsx("option", {
          value: "",
          children: "\u0412\u044B\u0431\u0435\u0440\u0438\u0442\u0435 \u0443\u0441\u043B\u0443\u0433\u0443"
        }), services.map(s => /*#__PURE__*/_jsx("option", {
          value: s.id,
          children: s.label
        }, s.id))]
      })
    }), /*#__PURE__*/_jsxs("fieldset", {
      children: [/*#__PURE__*/_jsx("legend", {
        className: "mb-1.5 text-sm font-medium text-gray-700",
        children: "\u041C\u0435\u0441\u0442\u043E \u043F\u0440\u0438\u0435\u043C\u0430"
      }), /*#__PURE__*/_jsx("div", {
        className: "flex gap-4",
        children: LOCATIONS.map(l => /*#__PURE__*/_jsxs("label", {
          className: "inline-flex cursor-pointer items-center",
          children: [/*#__PURE__*/_jsx("input", {
            type: "radio",
            value: l.value,
            ...register('location'),
            className: "h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900"
          }), /*#__PURE__*/_jsx("span", {
            className: "ml-2 text-sm text-gray-700",
            children: l.label
          })]
        }, l.value))
      })]
    }), location === 'home' && /*#__PURE__*/_jsx(Field, {
      label: "\u0410\u0434\u0440\u0435\u0441",
      error: errors.address?.message,
      children: /*#__PURE__*/_jsx("input", {
        ...register('address', {
          required: 'Укажите адрес',
          setValueAs: v => v.trim()
        }),
        className: inputClass,
        placeholder: "\u0443\u043B. \u041F\u0440\u0438\u043C\u0435\u0440\u043D\u0430\u044F, \u0434. 1"
      })
    }), /*#__PURE__*/_jsx(Field, {
      label: "\u0414\u0430\u0442\u0430 \u043F\u0440\u0438\u0435\u043C\u0430",
      error: errors.date?.message,
      children: /*#__PURE__*/_jsx("input", {
        type: "date",
        min: todayLocal(),
        ...register('date', {
          required: 'Укажите дату',
          validate: v => v >= todayLocal() || 'Дата не может быть в прошлом'
        }),
        className: inputClass
      })
    }), /*#__PURE__*/_jsx(Field, {
      label: "\u0412\u0440\u0435\u043C\u044F \u043F\u0440\u0438\u0435\u043C\u0430",
      error: errors.time?.message,
      children: /*#__PURE__*/_jsx("input", {
        type: "time",
        ...register('time', {
          required: 'Укажите время'
        }),
        className: inputClass
      })
    }), errors.root && /*#__PURE__*/_jsx("p", {
      className: "text-sm text-red-500",
      children: errors.root.message
    }), /*#__PURE__*/_jsx("div", {
      className: "border-t border-gray-100 pt-6",
      children: /*#__PURE__*/_jsx("button", {
        type: "submit",
        disabled: isSubmitting,
        className: "rounded-md bg-gray-900 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50",
        children: isSubmitting ? 'Отправка...' : 'Создать заявку'
      })
    })]
  });
}
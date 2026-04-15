import React from 'react';

export default function Checkbox({ checked, onChange, name, className = '', ...props }) {
    return (
        <input
            type="checkbox"
            name={name}
            checked={checked}
            onChange={onChange}
            className={`rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-500 dark:bg-gray-900 ${className}`}
            {...props}
        />
    );
}
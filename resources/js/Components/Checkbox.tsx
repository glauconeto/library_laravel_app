import React, { InputHTMLAttributes } from 'react';

interface CheckboxProps extends InputHTMLAttributes<HTMLInputElement> {
    checked?: boolean;
    onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
    name?: string;
    className?: string;
}

export default function Checkbox({ checked, onChange, name, className = '', ...props }: CheckboxProps) {
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
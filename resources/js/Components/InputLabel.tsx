import React, { LabelHTMLAttributes } from 'react';

interface InputLabelProps extends LabelHTMLAttributes<HTMLLabelElement> {
    value?: string;
    className?: string;
    children: React.ReactNode;
}

export default function InputLabel({ value, className = '', children, ...props }: InputLabelProps) {
    return (
        <label className={`block text-sm font-medium text-gray-700 dark:text-gray-300 ${className}`} {...props}>
            {value || children}
        </label>
    );
}
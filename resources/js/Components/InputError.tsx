import React, { HTMLAttributes } from 'react';

interface InputErrorProps extends HTMLAttributes<HTMLDivElement> {
    message?: string;
    className?: string;
}

export default function InputError({ message, className = '', ...props }: InputErrorProps) {
    if (!message) return null;

    return (
        <div className={`text-sm text-red-600 dark:text-red-400 ${className}`} {...props}>
            {message}
        </div>
    );
}
import { HTMLAttributes } from 'react';

interface InputErrorProps extends HTMLAttributes<HTMLDivElement> {
    message?: string;
    className?: string;
}

export default function InputError({ message, className = '', ...props }: InputErrorProps) {
    if (!message) return null;

    return (
        <div className={`flex items-start gap-1.5 text-sm text-red-600 dark:text-red-400 ${className}`} {...props}>
            <svg className="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
            </svg>
            <span>{message}</span>
        </div>
    );
}

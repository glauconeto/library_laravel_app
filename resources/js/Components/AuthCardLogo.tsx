import React from 'react';

interface AuthCardLogoProps {
    children?: React.ReactNode;
    className?: string;
}

export default function AuthCardLogo({ children, className = '' }: AuthCardLogoProps) {
    return (
        <div className={`text-center ${className}`}>
            <a href="/" className="text-2xl font-bold text-gray-900 dark:text-white">
                {children || 'Library'}
            </a>
        </div>
    );
}

import React, { HTMLAttributes } from 'react';

interface AuthCardProps extends HTMLAttributes<HTMLDivElement> {
    children: React.ReactNode;
    className?: string;
}

export default function AuthCard({ children, className = '', ...props }: AuthCardProps) {
    return (
        <div
            className={`min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 px-4 py-12 ${className}`}
            {...props}
        >
            <div className="w-full max-w-sm">
                <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
                    {children}
                </div>
            </div>
        </div>
    );
}

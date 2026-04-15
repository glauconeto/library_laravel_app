import React, { forwardRef } from 'react';

const Input = forwardRef((props, ref) => {
    const {
        type = 'text',
        className = '',
        ...rest
    } = props;

    return (
        <input
            type={type}
            ref={ref}
            className={`border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-indigo-500 dark:focus:ring-indigo-500 rounded-md shadow-sm ${className}`}
            {...rest}
        />
    );
});

Input.displayName = 'Input';

export default Input;
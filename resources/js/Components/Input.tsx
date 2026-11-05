import { forwardRef, InputHTMLAttributes } from 'react';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    type?: string;
    className?: string;
}

const Input = forwardRef<HTMLInputElement, InputProps>((props, ref) => {
    const {
        type = 'text',
        className = '',
        ...rest
    } = props;

    return (
        <input
            type={type}
            ref={ref}
            className={`border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 rounded-lg shadow-sm ${className}`}
            {...rest}
        />
    );
});

Input.displayName = 'Input';

export default Input;

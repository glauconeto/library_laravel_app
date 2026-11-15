import { forwardRef, InputHTMLAttributes } from 'react';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    type?: string;
    className?: string;
    isFocused?: boolean;
}

const Input = forwardRef<HTMLInputElement, InputProps>((props, ref) => {
    const {
        type = 'text',
        className = '',
        isFocused = false,
        autoFocus,
        ...rest
    } = props;

    return (
        <input
            type={type}
            ref={ref}
            autoFocus={isFocused || autoFocus}
            className={`border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-indigo-500 dark:focus:ring-indigo-500 rounded-md shadow-sm ${className}`}
            {...rest}
        />
    );
});

Input.displayName = 'Input';

export default Input;

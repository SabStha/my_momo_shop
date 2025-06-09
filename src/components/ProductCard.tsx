import { useState } from 'react';
import { Product } from '../types/product';
import { Link } from 'react-router-dom';

interface ProductCardProps {
  product: Product;
}

export const ProductCard = ({ product }: ProductCardProps) => {
  const [isHovered, setIsHovered] = useState<boolean>(false);

  return (
    <Link to={`/product/${product.id}`} className="block">
      <div
        className="relative overflow-hidden rounded-lg bg-white shadow-md transition-transform duration-300 hover:scale-105"
        onMouseEnter={() => setIsHovered(true)}
        onMouseLeave={() => setIsHovered(false)}
      >
        <div className="aspect-h-1 aspect-w-1 w-full overflow-hidden">
          <img
            src={product.image}
            alt={product.name}
            className="h-full w-full object-cover object-center"
          />
        </div>
        <div className="p-4">
          <h3 className="text-lg font-medium text-gray-900">{product.name}</h3>
          <p className="mt-1 text-sm text-gray-500">{product.description}</p>
          <div className="mt-2 flex items-center justify-between">
            <p className="text-lg font-bold text-gray-900">${product.price}</p>
            <button
              className={`rounded-full bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-200 ${
                isHovered ? 'bg-blue-700' : ''
              }`}
            >
              Add to Cart
            </button>
          </div>
        </div>
      </div>
    </Link>
  );
}; 
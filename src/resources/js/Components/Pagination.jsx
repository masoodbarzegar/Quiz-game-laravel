import React from 'react';
import { Link } from '@inertiajs/react'

export default function Pagination({ links }) {
  if (!links || links.length <= 3) {
    return null
  }

  return (
    <nav className="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
      <div className="flex w-0 flex-1">
        {links.map((link, i) => {
          if (i === 0) {
            return (
              <Link
                key={i}
                href={link.url}
                className={`inline-flex items-center border-t-2 px-4 py-2 text-sm font-medium ${
                  link.active
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                }`}
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            )
          }
          if (i === links.length - 1) {
            return (
              <Link
                key={i}
                href={link.url}
                className={`inline-flex items-center border-t-2 px-4 py-2 text-sm font-medium ${
                  link.active
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                }`}
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            )
          }
          if (link.url === null) {
            return (
              <span
                key={i}
                className="inline-flex items-center border-t-2 border-transparent px-4 py-2 text-sm font-medium text-gray-500"
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            )
          }
          return (
            <Link
              key={i}
              href={link.url}
              className={`inline-flex items-center border-t-2 px-4 py-2 text-sm font-medium ${
                link.active
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
              }`}
              dangerouslySetInnerHTML={{ __html: link.label }}
            />
          )
        })}
      </div>
    </nav>
  )
} 
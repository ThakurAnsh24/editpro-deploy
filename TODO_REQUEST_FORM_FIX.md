# Request Form Fix Plan

## Issue Analysis
The request.html form has critical bugs in form submission:
1. `phone` is undefined - uses `${phone}` but never gets value from input
2. Missing fields: `deliveryDate`, `payment`, `email`
3. Only stores to localStorage, never submits to backend

## Plan
1. Fix form submission handler in request.html
2. Replace the entire submit event listener block
3. Add backend submission via fetch API

## Status: IN PROGRESS

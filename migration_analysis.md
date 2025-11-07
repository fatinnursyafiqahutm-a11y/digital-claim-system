# Data Migration Analysis: 8 to 10 Category Structure

## Current State Analysis
- **Total Categories**: 8 (travel, medical, meals, office_supplies, training, communication, equipment, other)
- **Total Claims**: 4 claims across 3 categories
- **Category Distribution**:
  - travel: 2 claims
  - meals: 1 claim
  - medical: 0 claims
  - others: 0 claims each

## Required 10-Category Structure
1. Entertainment (Meals)
2. Accommodation
3. Transportation/Trip
4. Petrol
5. Toll
6. Phone Bills
7. Office Parking
8. Client Parking
9. Medical Claim
10. Other Claims

## Migration Strategy

### Phase 1: Category Mapping Analysis
**Current → New Mapping:**
- `travel` → `Transportation/Trip` (primary mapping)
- `meals` → `Entertainment (Meals)` (direct mapping)
- `medical` → `Medical Claim` (direct mapping)
- `office_supplies` → `Other Claims` (fallback)
- `training` → `Other Claims` (fallback)
- `communication` → Split mapping:
  - Phone-related → `Phone Bills`
  - Internet/other → `Other Claims`
- `equipment` → `Other Claims` (fallback)
- `other` → `Other Claims` (direct mapping)

### Phase 2: Data Integrity Requirements
1. **Claims Migration**: 4 existing claims need remapping
2. **Category Data**: Preserve existing category_data during migration
3. **Referential Integrity**: Maintain foreign key relationships
4. **Audit Trail**: Log all category changes for compliance

### Phase 3: Risk Mitigation
1. **Backup Strategy**: Full database backup before migration
2. **Rollback Plan**: Revert migration if issues arise
3. **Validation**: Post-migration data consistency checks
4. **Downtime**: Minimal downtime required (<5 minutes)

### Phase 4: Implementation Order
1. Create new categories
2. Migrate existing claims to new categories
3. Update dependent systems
4. Remove old categories
5. Validate data integrity

## Critical Business Rules to Clarify
1. **Travel Category Split**: How to handle existing travel claims that may contain accommodation, transport, petrol, toll components?
2. **Communication Split**: How to identify phone bills vs internet bills in existing communication claims?
3. **Parking Classification**: How to distinguish office vs client parking in existing claims?
4. **Category Data Preservation**: What specific fields need preservation from existing category_data?

## Migration Timeline Estimate
- **Planning**: 2 hours
- **Implementation**: 3-4 hours
- **Testing & Validation**: 2 hours
- **Total**: 6-8 hours